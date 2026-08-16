<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DemoDisclosureController extends Controller
{
    /**
     * Render the public software-demo disclosure page.
     *
     * This page exists so automated trust-and-safety scanners
     * (Netcraft, brand-protection bots, hosting providers) and
     * human reviewers can find an authoritative statement about
     * the nature of this domain in a single, deep-linkable URL.
     */
    public function __invoke(): View
    {
        $canonicalUrl = url('/demo-disclosure');
        $productName  = (string) config('app.demo_product_name', 'DigiKash');
        $vendorName   = (string) config('app.demo_vendor_name', 'Coevs');

        $payload = [
            'isDemo'       => (bool) config('app.demo', false),
            'appName'      => (string) config('app.name', $productName),
            'vendorName'   => $vendorName,
            'vendorUrl'    => (string) config('app.demo_vendor_url', ''),
            'productName'  => $productName,
            'salesUrl'     => (string) config('app.demo_sales_url', ''),
            'supportEmail' => (string) (setting('support_email') ?? config('mail.from.address') ?? ''),
            'canonicalUrl' => $canonicalUrl,
            'isBreadcrumb' => false,
            'meta'         => [
                'meta' => [
                    'title'         => __('Software Demo Disclosure').' | '.$productName,
                    'description'   => __('Authoritative disclosure stating this domain is a software product demo operated by :vendor — not a financial service or cryptocurrency investment platform.', ['vendor' => $vendorName]),
                    'keywords'      => 'software demo, disclosure, '.$productName.', '.$vendorName,
                    'canonical_url' => $canonicalUrl,
                    'robots'        => 'index,follow',
                ],
            ],
        ];

        return view('frontend.demo_disclosure', $payload);
    }
}
