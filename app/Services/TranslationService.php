<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JoeDixon\Translation\Drivers\Translation;

class TranslationService
{
    public function __construct(
        private readonly Translation $translation
    ) {}

    /**
     * Get all languages
     */
    public function allLanguages(): Collection
    {
        return $this->translation->allLanguages();
    }

    /**
     * Get groups for a language
     */
    public function getGroupsFor(string $language): Collection
    {
        return $this->translation->getGroupsFor($language);
    }

    /**
     * Add language
     */
    public function addLanguage(string $code, string $name): void
    {
        $this->translation->addLanguage($code, $name);
    }

    /**
     * Filter translations with error handling for server compatibility
     */
    public function filterTranslationsFor(string $languageCode, ?string $filter = null): Collection
    {
        try {
            // Try using the original method
            return $this->translation->filterTranslationsFor($languageCode, $filter);
        } catch (\Throwable $e) {
            // Fallback: Manual implementation to avoid reference passing issues
            return $this->manualFilterTranslations($languageCode, $filter);
        }
    }

    /**
     * Add translation
     */
    public function add($request, string $language, bool $isGroupTranslation): void
    {
        $this->translation->add($request, $language, $isGroupTranslation);
    }

    /**
     * Manual translation filtering to avoid reference passing issues
     */
    private function manualFilterTranslations(string $languageCode, ?string $filter = null): Collection
    {
        $langPath       = resource_path('lang');
        $appLocale      = config('app.locale', 'en');
        $fallbackLocale = config('app.fallback_locale', 'en');

        // Use fallback locale as reference, unless we're translating fallback itself
        $referenceLocale = ($languageCode === $fallbackLocale) ? $appLocale : $fallbackLocale;

        // Get single translations from JSON file
        $singleTranslations = $this->getSingleTranslations($langPath, $languageCode, $referenceLocale, $filter);

        // Get group translations from PHP files
        $groupTranslations = $this->getGroupTranslations($langPath, $languageCode, $referenceLocale, $filter);

        return new Collection([
            'single' => new Collection(['single' => $singleTranslations]),
            'group'  => $groupTranslations,
        ]);
    }

    /**
     * Get single translations from JSON file
     */
    private function getSingleTranslations(
        string $langPath,
        string $languageCode,
        string $fallbackLocale,
        ?string $filter
    ): Collection {
        $targetJsonFile   = "{$langPath}/{$languageCode}.json";
        $fallbackJsonFile = "{$langPath}/{$fallbackLocale}.json";

        $targetTranslations   = File::exists($targetJsonFile) ? json_decode(File::get($targetJsonFile), true)     ?? [] : [];
        $fallbackTranslations = File::exists($fallbackJsonFile) ? json_decode(File::get($fallbackJsonFile), true) ?? [] : [];

        // Merge keys from both files
        $allKeys      = array_unique(array_merge(array_keys($targetTranslations), array_keys($fallbackTranslations)));
        $translations = new Collection;

        foreach ($allKeys as $key) {
            $fallbackValue = $fallbackTranslations[$key] ?? $key;
            $targetValue   = $targetTranslations[$key]   ?? '';

            if ($filter && stripos($key, $filter) === false && stripos($fallbackValue, $filter) === false && stripos($targetValue, $filter) === false) {
                continue;
            }

            $translations[$key] = [
                $fallbackLocale => $fallbackValue,
                $languageCode   => $targetValue,
            ];
        }

        return $translations;
    }

    /**
     * Get group translations from PHP files
     */
    private function getGroupTranslations(
        string $langPath,
        string $languageCode,
        string $fallbackLocale,
        ?string $filter
    ): Collection {
        $targetGroupPath   = "{$langPath}/{$languageCode}";
        $fallbackGroupPath = "{$langPath}/{$fallbackLocale}";

        $groups = new Collection;

        // Get all group files from both directories
        $allGroups = collect();

        if (File::isDirectory($fallbackGroupPath)) {
            foreach (File::files($fallbackGroupPath) as $file) {
                $allGroups->push($file->getFilenameWithoutExtension());
            }
        }

        if (File::isDirectory($targetGroupPath)) {
            foreach (File::files($targetGroupPath) as $file) {
                $allGroups->push($file->getFilenameWithoutExtension());
            }
        }

        $allGroups = $allGroups->unique();

        foreach ($allGroups as $groupName) {
            $fallbackFile = "{$fallbackGroupPath}/{$groupName}.php";
            $targetFile   = "{$targetGroupPath}/{$groupName}.php";

            $fallbackTranslations = File::exists($fallbackFile) ? File::getRequire($fallbackFile) : [];
            $targetTranslations   = File::exists($targetFile) ? File::getRequire($targetFile) : [];

            $mergedTranslations = $this->mergeTranslationsWithLanguages(
                $fallbackTranslations,
                $targetTranslations,
                $fallbackLocale,
                $languageCode,
                $filter
            );

            if (! empty($mergedTranslations)) {
                $groups[$groupName] = $mergedTranslations;
            }
        }

        return $groups;
    }

    /**
     * Merge translations from fallback and target languages
     */
    private function mergeTranslationsWithLanguages(
        array $fallbackArray,
        array $targetArray,
        string $fallbackLocale,
        string $targetLocale,
        ?string $filter,
        string $prefix = ''
    ): array {
        $result = [];

        // Get all keys from both arrays
        $allKeys = array_unique(array_merge(array_keys($fallbackArray), array_keys($targetArray)));

        foreach ($allKeys as $key) {
            $fallbackValue = $fallbackArray[$key] ?? '';
            $targetValue   = $targetArray[$key]   ?? '';
            $fullKey       = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($fallbackValue) || is_array($targetValue)) {
                // Handle nested arrays
                $fallbackNested = is_array($fallbackValue) ? $fallbackValue : [];
                $targetNested   = is_array($targetValue) ? $targetValue : [];

                $nested = $this->mergeTranslationsWithLanguages(
                    $fallbackNested,
                    $targetNested,
                    $fallbackLocale,
                    $targetLocale,
                    $filter,
                    $fullKey
                );

                if (! empty($nested)) {
                    $result[$key] = $nested;
                }
            } else {
                // Handle string values
                if ($filter && stripos($key, $filter) === false && stripos((string) $fallbackValue, $filter) === false && stripos((string) $targetValue, $filter) === false) {
                    continue;
                }

                $result[$key] = [
                    $fallbackLocale => $fallbackValue ?: $key,
                    $targetLocale   => $targetValue,
                ];
            }
        }

        return $result;
    }
}
