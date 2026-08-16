<?php

namespace App\Http\Controllers\Backend;

use App\Enums\PageType;
use App\Http\Requests\Page\PageRequest;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageComponent;
use App\Traits\FileManageTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends BaseController
{
    use FileManageTrait;

    public static function permissions(): array
    {
        return [
            'index'        => 'page-list',
            'create|store' => 'page-create',
            'edit|update'  => 'page-edit',
            'destroy'      => 'page-delete',

        ];
    }

    /*
     * Retrieves all pages and renders the page index view.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $pages = Page::all();

        return view('backend.page.index', [
            'pages' => $pages,
            ...$this->pageManagerHeroMetrics($pages),
        ]);
    }

    /**
     * Renders the page create view.
     */
    public function create(): View
    {
        /** @var Collection $components */
        $components = PageComponent::active()
            ->forTheme(activeTheme())
            ->whereNull('page_id')
            ->get()
            ->sortBy('sort')
            ->values();

        $locales = Language::languageGet();

        return view('backend.page.create', [
            'components'      => $components,
            'locales'         => $locales,
            'totalComponents' => $components->count(),
            'totalLocales'    => is_countable($locales) ? count($locales) : 0,
            'defaultLocale'   => strtoupper((string) app()->getDefaultLocale()),
        ]);
    }

    /**
     * Creates a new page.
     *
     * @return RedirectResponse
     */
    public function store(PageRequest $request)
    {
        $validated = $request->validated();

        $breadcrumb = $request->hasFile('breadcrumb') ? $this->uploadImage($request->file('breadcrumb')) : null;

        // Store the page
        Page::create([
            'title'         => $validated['page_title'],
            'slug'          => Str::slug($validated['page_slug']),
            'component_ids' => $validated['component'],
            'type'          => PageType::Dynamic,
            'is_active'     => $validated['is_active'] ?? false,
            'breadcrumb'    => $breadcrumb,
            'is_breadcrumb' => $validated['is_breadcrumb'] ?? false,
        ]);

        notifyEvs('success', __('Page created successfully.'));

        return redirect()->route('admin.page.site.index');
    }

    /**
     * Renders the page edit view.
     */
    public function edit($id): View
    {
        $page = Page::find($id);

        abort_if($page === null, 404);

        // Load all components to show in list, scoped to the active theme so
        // the builder library matches the visual theme the site is using.
        $activeTheme = activeTheme();

        $components = PageComponent::active()
            ->forTheme($activeTheme)
            ->where('page_id', $page->id)
            ->get();

        if ($components->isEmpty()) {
            $components = PageComponent::active()
                ->forTheme($activeTheme)
                ->whereNull('page_id')
                ->get();
        }

        $locales = Language::languageGet();

        $componentIds = is_array($page->component_ids) ? $page->component_ids : [];

        return view('backend.page.edit', [
            'page'            => $page,
            'components'      => $components,
            'locales'         => $locales,
            'totalComponents' => count($componentIds),
            'totalLocales'    => is_countable($locales) ? count($locales) : 0,
            'isProtected'     => (bool) $page->is_protected,
        ]);
    }

    /**
     * Updates the page.
     *
     * @return RedirectResponse
     */
    public function update(PageRequest $request, $id)
    {
        $page      = Page::find($id);
        $validated = $request->validated();

        if (isset($validated['breadcrumb']) && $validated['breadcrumb'] === 'coevs-remove' && $page->breadcrumb) {
            $this->delete($page->breadcrumb);
            $validated['breadcrumb'] = null;
        } else {
            $breadcrumb = $request->hasFile('breadcrumb') ? $this->uploadImage($request->file('breadcrumb')) : $page->breadcrumb;
        }

        $isActive     = $page->is_home ? true : $validated['is_active']      ?? false;
        $isBreadcrumb = $page->is_home ? false : $validated['is_breadcrumb'] ?? false;

        $page->update([
            'title'         => $validated['page_title'],
            'slug'          => $page->is_protected ? $page->slug : Str::slug($validated['page_slug']),
            'component_ids' => $validated['component'],
            'breadcrumb'    => $breadcrumb ?? null,
            'is_breadcrumb' => $isBreadcrumb,
            'is_active'     => $isActive,
        ]);

        notifyEvs('success', __('Page updated successfully.'));

        return redirect()->route('admin.page.site.index');
    }

    /**
     * Compute the hero / KPI metrics the Page Manager index view consumes.
     *
     * Lives here (controller orchestration) instead of inside an @php block
     * at the top of the blade so a stale compiled view or partial-payload
     * regression can't leave the template with undefined variables.
     *
     * @param Collection<int, Page> $pages
     * @return array{
     *   totalPages: int,
     *   activePages: int,
     *   dynamicPages: int,
     *   protectedPages: int,
     *   homePages: int,
     *   totalComponents: int
     * }
     */
    protected function pageManagerHeroMetrics(Collection $pages): array
    {
        $totalPages   = $pages->count();
        $dynamicPages = $pages->filter(fn (Page $page) => $page->type === PageType::Dynamic)->count();

        return [
            'totalPages'      => $totalPages,
            'activePages'     => $pages->where('is_active', true)->count(),
            'dynamicPages'    => $dynamicPages,
            'protectedPages'  => $totalPages - $dynamicPages,
            'homePages'       => $pages->where('is_home', true)->count(),
            'totalComponents' => (int) $pages->sum(fn (Page $page) => is_array($page->component_ids) ? count($page->component_ids) : 0),
        ];
    }

    public function destroy($id)
    {
        $page = Page::find($id);

        if ($page->seo) {
            $page->seo->delete();
        }
        if ($page->type === PageType::Static) {
            notifyEvs('error', __('Static pages cannot be deleted.'));

            return redirect()->back();
        }

        if ($page->breadcrumb) {
            $this->delete($page->breadcrumb);
        }

        $page->delete();
        notifyEvs('success', __('Page Deleted Successfully'));

        return redirect()->back();
    }
}
