@extends('backend.landings.layout')

@section('title', __('Custom Landing Guide'))
@section('sub_title', __('How Custom Landing Works'))
@section('sub_subtitle', __('Admin guide for uploading, validating, previewing, and publishing landing pages.'))

@section('sub_action')
    <a href="{{ route('admin.custom-landing.create') }}" class="btn btn-primary cla-btn">
        <x-icon name="plus" height="20" width="20"/>
        <span>@lang('Upload Landing')</span>
    </a>
    <a href="{{ route('admin.custom-landing.index') }}" class="btn btn-light cla-btn">
        <x-icon name="back" height="20" width="20"/>
        <span>@lang('Back')</span>
    </a>
@endsection

@section('sub_content')
    @php
        $variableRows = [
            ['name' => '{app_name}', 'description' => 'Configured application name.', 'example' => 'DigiKash'],
            ['name' => '{folder}', 'description' => 'Published landing folder name.', 'example' => 'virtual-card-landing-123'],
            ['name' => '{home_url}', 'description' => 'Public homepage URL.', 'example' => '/'],
            ['name' => '{user_login_url}', 'description' => 'User login page URL.', 'example' => '/login'],
            ['name' => '{user_register_url}', 'description' => 'User registration page URL.', 'example' => '/register'],
            ['name' => '{user_dashboard_url}', 'description' => 'Authenticated user dashboard URL.', 'example' => '/user/dashboard'],
            ['name' => '{user_wallet_url}', 'description' => 'User wallet page URL.', 'example' => '/user/wallet/list'],
            ['name' => '{user_deposit_url}', 'description' => 'Deposit money page URL.', 'example' => '/user/deposit/create'],
            ['name' => '{user_send_money_url}', 'description' => 'Send money page URL.', 'example' => '/user/send-money/create'],
            ['name' => '{user_support_url}', 'description' => 'Support ticket page URL.', 'example' => '/user/support-ticket'],
            ['name' => '{merchant_login_url}', 'description' => 'Merchant login page URL.', 'example' => '/merchant/login'],
            ['name' => '{merchant_register_url}', 'description' => 'Merchant registration page URL.', 'example' => '/merchant/register'],
            ['name' => '{agent_login_url}', 'description' => 'Agent login page URL.', 'example' => '/agent/login'],
            ['name' => '{agent_register_url}', 'description' => 'Agent registration page URL.', 'example' => '/agent/register'],
        ];

        $actionRows = [
            ['name' => 'user-login', 'target' => 'Login CTA', 'use' => 'Send visitors to the user login page.'],
            ['name' => 'user-register', 'target' => 'Registration CTA', 'use' => 'Start a new user account flow.'],
            ['name' => 'user-dashboard', 'target' => 'Dashboard CTA', 'use' => 'Take authenticated users to the dashboard.'],
            ['name' => 'user-wallet', 'target' => 'Wallet CTA', 'use' => 'Open the user wallet area.'],
            ['name' => 'user-deposit', 'target' => 'Deposit CTA', 'use' => 'Route visitors to the deposit money flow.'],
            ['name' => 'user-send-money', 'target' => 'Send Money CTA', 'use' => 'Route visitors to the send money flow.'],
            ['name' => 'user-support', 'target' => 'Support CTA', 'use' => 'Open support ticket access.'],
            ['name' => 'merchant-login', 'target' => 'Merchant Login', 'use' => 'Send merchants to their login page.'],
            ['name' => 'merchant-register', 'target' => 'Merchant Signup', 'use' => 'Start merchant onboarding.'],
            ['name' => 'agent-login', 'target' => 'Agent Login', 'use' => 'Send agents to their login page.'],
            ['name' => 'agent-register', 'target' => 'Agent Signup', 'use' => 'Start agent onboarding.'],
            ['name' => 'home', 'target' => 'Home', 'use' => 'Return visitors to the public homepage.'],
        ];
    @endphp

    <section class="cla-guide-hero">
        <div class="cla-guide-hero__content">
            <span class="cla-guide-eyebrow">@lang('International Guide')</span>
            <h2>@lang('Build campaign landings that connect directly to DigiKash product flows.')</h2>
            <p>
                @lang('Use variables for stable URLs, action names for smart buttons, and the upload validator to publish a secure static landing as the public homepage.')
            </p>
        </div>
        <div class="cla-guide-hero__panel">
            <div class="cla-guide-signal cla-guide-signal--success">
                <strong>@lang('Variables')</strong>
                <span>@lang('Use placeholders such as {user_login_url} and {user_register_url}.')</span>
            </div>
            <div class="cla-guide-signal cla-guide-signal--info">
                <strong>@lang('Action Bridge')</strong>
                <span>@lang('Use data-dk-action for login, signup, dashboard, wallet, and support buttons.')</span>
            </div>
            <div class="cla-guide-signal cla-guide-signal--warning">
                <strong>@lang('Safe Publishing')</strong>
                <span>@lang('Every uploaded bundle is validated before replacing the live homepage.')</span>
            </div>
        </div>
    </section>

    <section class="cla-guide-section">
        <div class="cla-guide-section__head">
            <span class="cla-guide-eyebrow">@lang('Quick Start')</span>
            <h3>@lang('Recommended Workflow')</h3>
        </div>

        <div class="cla-guide-steps">
            <article class="cla-guide-step">
                <span class="cla-guide-step__number">1</span>
                <h4>@lang('Prepare the ZIP Bundle')</h4>
                <p>@lang('Keep index.html at the ZIP root and organize CSS, JavaScript, images, and fonts in clear folders.')</p>
                <pre class="cla-guide-code">/index.html
/css/style.css
/js/app.js
/images/hero.webp</pre>
            </article>
            <article class="cla-guide-step">
                <span class="cla-guide-step__number">2</span>
                <h4>@lang('Add Variables')</h4>
                <p>@lang('Use URL placeholders for CTA links that must stay valid across environments.')</p>
                <pre class="cla-guide-code">&lt;a href="{user_register_url}"&gt;
  Create Account
&lt;/a&gt;</pre>
            </article>
            <article class="cla-guide-step">
                <span class="cla-guide-step__number">3</span>
                <h4>@lang('Add Smart Actions')</h4>
                <p>@lang('Use data-dk-action when a button should be handled by the landing action bridge.')</p>
                <pre class="cla-guide-code">&lt;button data-dk-action="user-login"&gt;
  Login
&lt;/button&gt;</pre>
            </article>
            <article class="cla-guide-step">
                <span class="cla-guide-step__number">4</span>
                <h4>@lang('Preview and Activate')</h4>
                <p>@lang('Preview responsive layout, CTA routes, assets, and browser console output before activation.')</p>
            </article>
        </div>
    </section>

    <section class="cla-guide-section">
        <div class="cla-guide-section__head">
            <span class="cla-guide-eyebrow">@lang('Reference')</span>
            <h3>@lang('Variables and Action Names')</h3>
        </div>

        <div class="cla-guide-reference">
            <article class="cla-guide-panel">
                <div class="cla-guide-panel__head">
                    <span class="cla-guide-card__icon">
                        <x-icon name="file" height="22" width="22"/>
                    </span>
                    <div>
                        <h3>@lang('Variable Reference')</h3>
                        <p>@lang('Use these placeholders in href values, copy, or asset paths. The compiler replaces them during upload or HTML save.')</p>
                    </div>
                </div>

                <div class="cla-guide-note">
                    <strong>@lang('Supported placeholder formats')</strong>
                    <span><code>{user_login_url}</code> <code>@{{user_login_url}}</code> <code>@{{ user_login_url }}</code></span>
                </div>

                <div class="cla-guide-table-wrap">
                    <table class="cla-guide-table">
                        <thead>
                            <tr>
                                <th>@lang('Variable')</th>
                                <th>@lang('Purpose')</th>
                                <th>@lang('Example')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($variableRows as $row)
                                <tr>
                                    <td><code>{{ $row['name'] }}</code></td>
                                    <td>{{ __($row['description']) }}</td>
                                    <td><span class="cla-guide-table__muted">{{ $row['example'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="cla-guide-panel">
                <div class="cla-guide-panel__head">
                    <span class="cla-guide-card__icon">
                        <x-icon name="play" height="22" width="22"/>
                    </span>
                    <div>
                        <h3>@lang('Action Name Reference')</h3>
                        <p>@lang('Use action names with data-dk-action. The bridge script resolves the correct app route and handles the click.')</p>
                    </div>
                </div>

                <div class="cla-guide-table-wrap">
                    <table class="cla-guide-table">
                        <thead>
                            <tr>
                                <th>@lang('Action')</th>
                                <th>@lang('CTA Type')</th>
                                <th>@lang('Recommended Use')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($actionRows as $row)
                                <tr>
                                    <td><code>{{ $row['name'] }}</code></td>
                                    <td>{{ __($row['target']) }}</td>
                                    <td>{{ __($row['use']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </section>

    <section class="cla-guide-section">
        <div class="cla-guide-section__head">
            <span class="cla-guide-eyebrow">@lang('Examples')</span>
            <h3>@lang('Implementation Patterns')</h3>
        </div>

        <div class="cla-guide-patterns">
            <article class="cla-guide-card">
                <span class="cla-guide-card__icon">
                    <x-icon name="home" height="22" width="22"/>
                </span>
                <h3>@lang('Primary Campaign CTA')</h3>
                <p>@lang('Best for hero buttons and repeated signup links.')</p>
                <pre class="cla-guide-code">&lt;a class="cta-primary"
   href="{user_register_url}"
   data-dk-action="user-register"&gt;
  Create Account
&lt;/a&gt;</pre>
            </article>

            <article class="cla-guide-card">
                <span class="cla-guide-card__icon">
                    <x-icon name="shield" height="22" width="22"/>
                </span>
                <h3>@lang('Authenticated Feature CTA')</h3>
                <p>@lang('Best for product campaigns that point existing users to a specific feature.')</p>
                <pre class="cla-guide-code">&lt;button data-dk-action="user-deposit"&gt;
  Deposit Now
&lt;/button&gt;

&lt;button data-dk-action="user-send-money"&gt;
  Send Money
&lt;/button&gt;</pre>
            </article>

            <article class="cla-guide-card">
                <span class="cla-guide-card__icon">
                    <x-icon name="file" height="22" width="22"/>
                </span>
                <h3>@lang('Asset Path Convention')</h3>
                <p>@lang('Use the folder variable for assets published inside the landing folder.')</p>
                <pre class="cla-guide-code">&lt;link rel="stylesheet"
  href="/custom-landings/{folder}/css/style.css"&gt;

&lt;script src="/custom-landings/{folder}/js/app.js"&gt;&lt;/script&gt;</pre>
            </article>
        </div>
    </section>

    <section class="cla-guide-grid">
        <article class="cla-guide-card">
            <span class="cla-guide-card__icon">
                <x-icon name="shield" height="22" width="22"/>
            </span>
            <h3>@lang('Security Guardrails')</h3>
            <ul>
                <li>@lang('Only approved static file types are accepted.')</li>
                <li>@lang('Traversal paths, hidden dotfiles, PHP files, and unsafe links are rejected.')</li>
                <li>@lang('Existing files are replaced only after the new bundle passes validation.')</li>
                <li>@lang('Security headers are applied when the homepage serves a custom landing.')</li>
            </ul>
        </article>

        <article class="cla-guide-card">
            <span class="cla-guide-card__icon">
                <x-icon name="home" height="22" width="22"/>
            </span>
            <h3>@lang('Homepage Resolution')</h3>
            <ul>
                <li>@lang('If an active landing has index.html, the root URL (/) displays the custom campaign.')</li>
                <li>@lang('If no landing is active, the regular CMS homepage flow continues.')</li>
                <li>@lang('Deleting a landing also removes its published folder.')</li>
            </ul>
        </article>

        <article class="cla-guide-card">
            <span class="cla-guide-card__icon">
                <x-icon name="info" height="22" width="22"/>
            </span>
            <h3>@lang('Bridge Behavior')</h3>
            <ul>
                <li>@lang('The bridge script is injected automatically during upload and HTML save.')</li>
                <li>@lang('Existing bridge markup is replaced so duplicate scripts are not created.')</li>
                <li>@lang('Unknown action names are marked with data-dk-action-missing for debugging.')</li>
            </ul>
        </article>
    </section>

    <section class="cla-guide-section">
        <div class="cla-guide-section__head">
            <span class="cla-guide-eyebrow">@lang('Best Practice')</span>
            <h3>@lang('Production Readiness Checklist')</h3>
        </div>

        <div class="cla-guide-checklist">
            <label><span class="cla-check-dot"></span>@lang('Check desktop, tablet, and mobile layouts.')</label>
            <label><span class="cla-check-dot"></span>@lang('Verify CTA links such as /register, /login, or campaign-specific routes.')</label>
            <label><span class="cla-check-dot"></span>@lang('Optimize image weight before upload.')</label>
            <label><span class="cla-check-dot"></span>@lang('Confirm that variables are replaced and action buttons navigate correctly.')</label>
            <label><span class="cla-check-dot"></span>@lang('Check browser console, image, CSS, and JavaScript paths.')</label>
            <label><span class="cla-check-dot"></span>@lang('Publish only trusted HTML and JavaScript because the page runs on your domain.')</label>
        </div>
    </section>
@endsection
