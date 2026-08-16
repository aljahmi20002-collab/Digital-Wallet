<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\CustomLanding\StoreCustomLandingRequest;
use App\Http\Requests\CustomLanding\UpdateCustomLandingRequest;
use App\Http\Requests\CustomLanding\UpdateLandingHtmlRequest;
use App\Models\CustomLanding;
use App\Services\CustomLandingArchiveService;
use App\Services\CustomLandingHtmlCompiler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CustomLandingController extends BaseController
{
    public function __construct(
        private readonly CustomLandingArchiveService $archives,
        private readonly CustomLandingHtmlCompiler $htmlCompiler
    ) {}

    public static function permissions(): array
    {
        return [
            'guide|index|show'                                                      => 'custom-landing-list',
            'activate|create|destroy|edit|manageHtml|manageHtmlUpdate|store|update' => 'custom-landing-manage',
        ];
    }

    public function index(): View
    {
        $landings = CustomLanding::query()
            ->latest()
            ->paginate(12);

        $metrics = [
            'total'       => CustomLanding::query()->count(),
            'active'      => CustomLanding::query()->active()->count(),
            'files'       => CustomLanding::query()->sum('file_count'),
            'storageSize' => CustomLanding::query()->sum('total_size'),
        ];

        return view('backend.landings.index', compact('landings', 'metrics'));
    }

    public function create(): View
    {
        return view('backend.landings.create');
    }

    public function guide(): View
    {
        return view('backend.landings.guide');
    }

    public function store(StoreCustomLandingRequest $request): RedirectResponse
    {
        $validated       = $request->validated();
        $folder          = Str::slug($validated['name']).'-'.time();
        $preparedLanding = null;
        $targetPath      = public_path("custom-landings/{$folder}");

        try {
            $preparedLanding = $this->archives->prepare($request->file('zipFile'), $folder);
            $this->archives->publishPreparedDirectory($preparedLanding['path'], $targetPath);

            DB::transaction(function () use ($folder, $preparedLanding, $validated): void {
                CustomLanding::query()->update(['status' => false]);

                CustomLanding::query()->create([
                    'name'              => $validated['name'],
                    'folder'            => $folder,
                    'status'            => true,
                    'file_count'        => $preparedLanding['file_count'],
                    'total_size'        => $preparedLanding['total_size'],
                    'source_checksum'   => $preparedLanding['source_checksum'],
                    'published_at'      => now(),
                    'last_validated_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            $this->archives->discard($preparedLanding['path'] ?? null);

            if (File::isDirectory($targetPath)) {
                File::deleteDirectory($targetPath);
            }

            throw $e;
        }

        notifyEvs('success', __('Landing uploaded, validated, and published successfully.'));

        return redirect()->route('admin.custom-landing.index');
    }

    public function show(CustomLanding $customLanding): View
    {
        return view('backend.landings.show', [
            'landing_page' => $customLanding,
        ]);
    }

    public function edit(CustomLanding $customLanding): View
    {
        return view('backend.landings.edit', [
            'landing_page' => $customLanding,
        ]);
    }

    public function update(UpdateCustomLandingRequest $request, CustomLanding $customLanding): RedirectResponse
    {
        $validated       = $request->validated();
        $preparedLanding = null;
        $fileMetadata    = [];

        if ($request->hasFile('zipFile')) {
            $preparedLanding = $this->archives->prepare($request->file('zipFile'), $customLanding->folder);
            $this->archives->publishPreparedDirectory($preparedLanding['path'], $customLanding->landingPath());

            $fileMetadata = [
                'file_count'        => $preparedLanding['file_count'],
                'total_size'        => $preparedLanding['total_size'],
                'source_checksum'   => $preparedLanding['source_checksum'],
                'last_validated_at' => now(),
            ];
        }

        DB::transaction(function () use ($customLanding, $fileMetadata, $validated): void {
            if ((bool) $validated['status']) {
                CustomLanding::query()
                    ->whereKeyNot($customLanding->id)
                    ->update(['status' => false]);
            }

            $customLanding->update([
                ...$fileMetadata,
                'name'         => $validated['name'],
                'published_at' => (bool) $validated['status'] ? ($customLanding->published_at ?? now()) : $customLanding->published_at,
                'status'       => (bool) $validated['status'],
            ]);
        });

        notifyEvs('success', __('Landing updated successfully.'));

        return redirect()->route('admin.custom-landing.index');
    }

    public function activate(CustomLanding $customLanding): RedirectResponse
    {
        if (! $customLanding->hasIndexFile()) {
            notifyEvs('error', __('This landing cannot be activated because index.html is missing.'));

            return redirect()->back();
        }

        DB::transaction(function () use ($customLanding): void {
            CustomLanding::query()
                ->whereKeyNot($customLanding->id)
                ->update(['status' => false]);

            $customLanding->update([
                'published_at' => $customLanding->published_at ?? now(),
                'status'       => true,
            ]);
        });

        notifyEvs('success', __('Landing activated successfully.'));

        return redirect()->route('admin.custom-landing.index');
    }

    public function manageHtml(CustomLanding $customLanding): View
    {
        $indexPath = $customLanding->indexPath();
        $content   = File::exists($indexPath) ? $this->htmlCompiler->stripBridge(File::get($indexPath)) : '';

        return view('backend.landings.manage_html', [
            'content'      => $content,
            'landing_page' => $customLanding,
        ]);
    }

    public function manageHtmlUpdate(UpdateLandingHtmlRequest $request, CustomLanding $customLanding): RedirectResponse
    {
        File::ensureDirectoryExists($customLanding->landingPath(), 0755, true);
        File::put(
            $customLanding->indexPath(),
            $this->htmlCompiler->compileForPublish($request->validated('htmlContent'), $customLanding->folder)
        );

        $customLanding->update([
            'html_updated_at'   => now(),
            'last_validated_at' => now(),
        ]);

        notifyEvs('success', __('HTML updated successfully.'));

        return redirect()->back();
    }

    public function destroy(CustomLanding $customLanding): RedirectResponse
    {
        File::deleteDirectory($customLanding->landingPath());
        $customLanding->delete();

        notifyEvs('success', __('Landing deleted successfully.'));

        return redirect()->route('admin.custom-landing.index');
    }
}
