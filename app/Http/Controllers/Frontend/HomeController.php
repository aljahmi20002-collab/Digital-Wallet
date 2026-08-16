<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CustomLanding;
use App\Models\Page;
use App\Services\PageMetaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HomeController extends Controller
{
    public function __invoke(): View|RedirectResponse|BinaryFileResponse
    {

        $activeLanding = CustomLanding::getActiveLanding();

        if ($activeLanding && $activeLanding->hasIndexFile()) {
            return response()->file($activeLanding->indexPath(), [
                'Cache-Control'           => 'no-cache, private',
                'Content-Security-Policy' => "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; img-src 'self' data: https:; font-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'",
                'Content-Type'            => 'text/html; charset=UTF-8',
                'X-Content-Type-Options'  => 'nosniff',
            ]);
        }

        // Redirect to the custom home path if it's set and not the default '/'
        $homeRedirect = setting('home_redirect');

        if ($homeRedirect && $homeRedirect !== '/') {
            return Redirect::to($homeRedirect);
        }

        $page = Page::home();
        $meta = PageMetaService::build($page);

        $components   = $page->components;
        $isBreadcrumb = $page->is_breadcrumb;

        $locale = app()->getLocale();

        return view('frontend.pages.index', compact('page', 'isBreadcrumb', 'components', 'meta', 'locale'));
    }
}
