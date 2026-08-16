<?php

namespace App\Support;

class WithdrawFieldNormalizer
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function normalize(mixed $schema): array
    {
        $schema = self::decode($schema);

        if (is_string($schema)) {
            $schema = trim($schema);

            return $schema === '' ? [] : [self::fieldFromDefinition($schema, [])];
        }

        if (! is_array($schema) || $schema === []) {
            return [];
        }

        $fields = [];

        if (array_is_list($schema)) {
            foreach ($schema as $index => $definition) {
                $field = self::fieldFromDefinition($index, $definition);

                if ($field !== null) {
                    $fields[] = $field;
                }
            }

            return $fields;
        }

        foreach ($schema as $name => $definition) {
            $field = self::fieldFromDefinition($name, $definition);

            if ($field !== null) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @return array<string, mixed>
     */
    public static function values(mixed $credentials): array
    {
        $credentials = self::decode($credentials);

        if (! is_array($credentials)) {
            return [];
        }

        $values = [];

        foreach ($credentials as $key => $credential) {
            if (is_array($credential) && isset($credential['name'])) {
                $name = trim((string) $credential['name']);

                if ($name !== '') {
                    $values[$name] = $credential['value'] ?? null;
                }

                continue;
            }

            if (is_string($key)) {
                $values[$key] = is_array($credential) && array_key_exists('value', $credential)
                    ? $credential['value']
                    : $credential;
            }
        }

        return array_map(
            fn (mixed $value): mixed => is_string($value) ? trim($value) : $value,
            $values
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(mixed $schema, string $prefix = 'credentials.', bool $preserveExistingFiles = false): array
    {
        $rules = [];

        foreach (self::normalize($schema) as $field) {
            $name = (string) ($field['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $required = ($field['validation'] ?? 'required') === 'required';
            $type     = (string) ($field['type'] ?? 'text');
            $hasValue = array_key_exists('value', $field) && filled($field['value']);
            $rule     = [$required ? 'required' : 'nullable'];

            if ($type === 'file' && $preserveExistingFiles && $hasValue) {
                $rule = ['nullable'];
            }

            array_push($rule, ...match ($type) {
                'file'     => ['file', 'max:2048'],
                'textarea' => ['string', 'max:2000'],
                default    => ['string', 'max:255'],
            });

            $rules[$prefix.$name] = $rule;
        }

        return $rules;
    }

    private static function decode(mixed $value): mixed
    {
        while (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return [];
            }

            $decoded = json_decode($trimmed, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $value;
            }

            $value = $decoded;
        }

        return $value;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function fieldFromDefinition(int|string $key, mixed $definition): ?array
    {
        if (is_string($definition)) {
            $name       = $definition;
            $definition = [];
        } elseif (is_array($definition)) {
            $name = $definition['name'] ?? (is_string($key) ? $key : null);
        } else {
            return null;
        }

        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        $type = (string) ($definition['type'] ?? 'text');

        if (! in_array($type, ['text', 'textarea', 'file', 'select'], true)) {
            $type = 'text';
        }

        $validation = $definition['validation'] ?? null;

        if (! in_array($validation, ['required', 'nullable'], true)) {
            $validation = (bool) ($definition['required'] ?? true) ? 'required' : 'nullable';
        }

        $field = [
            'name'       => $name,
            'type'       => $type,
            'validation' => $validation,
        ];

        foreach (['label', 'placeholder', 'value'] as $optionalKey) {
            if (array_key_exists($optionalKey, $definition)) {
                $field[$optionalKey] = $definition[$optionalKey];
            }
        }

        if (isset($definition['options']) && is_array($definition['options'])) {
            $field['options'] = $definition['options'];
        }

        return $field;
    }
}
