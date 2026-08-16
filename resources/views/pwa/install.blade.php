<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="{{ $themeColor }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="robots" content="noindex,nofollow">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <title>{{ __('Install App') }} | {{ $siteTitle }}</title>
    <style>
        :root {
            color-scheme: light;
            --dk-primary: {{ $themeColor }};
            --dk-bg: {{ $backgroundColor }};
            --dk-card: #ffffff;
            --dk-text: #152033;
            --dk-muted: #65758b;
            --dk-line: #dfe8ee;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 22px;
            background: var(--dk-bg);
            color: var(--dk-text);
            font-family: "Plus Jakarta Sans", Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .dk-install-page {
            width: min(100%, 430px);
            border: 1px solid var(--dk-line);
            border-radius: 16px;
            padding: 26px;
            background: var(--dk-card);
            box-shadow: 0 18px 54px rgba(15, 23, 42, 0.1);
        }

        .dk-install-brand {
            width: 58px;
            height: 58px;
            border-radius: 15px;
            display: grid;
            place-items: center;
            margin-bottom: 18px;
            background: var(--dk-primary);
            color: #ffffff;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .dk-install-brand img {
            width: 100%;
            height: 100%;
            border-radius: inherit;
            object-fit: cover;
        }

        h1 {
            margin: 0;
            font-size: 23px;
            line-height: 1.25;
            letter-spacing: 0;
        }

        .dk-install-status {
            margin: 10px 0 22px;
            min-height: 46px;
            color: var(--dk-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .dk-install-actions {
            display: grid;
            gap: 10px;
        }

        button,
        a {
            min-height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        button {
            border: 0;
            background: var(--dk-primary);
            color: #ffffff;
            cursor: pointer;
        }

        button:disabled {
            cursor: progress;
            opacity: 0.68;
        }

        a {
            border: 1px solid var(--dk-line);
            background: #ffffff;
            color: var(--dk-text);
        }

        .dk-install-manual {
            margin: 18px 0 0;
            padding: 14px;
            border-radius: 12px;
            background: #f8fafc;
            color: var(--dk-muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .dk-install-manual[hidden] {
            display: none;
        }
    </style>
</head>
<body>
    <main class="dk-install-page" data-dk-install-page data-dk-return-url="{{ $returnUrl }}">
        <div class="dk-install-brand" aria-hidden="true">
            <img src="{{ $iconUrl }}" alt="" loading="lazy">
        </div>
        <h1>{{ __('Install :name', ['name' => $siteTitle]) }}</h1>
        <p class="dk-install-status" data-dk-install-status>{{ __('Preparing the app install prompt...') }}</p>

        <div class="dk-install-actions">
            <button type="button" data-dk-install-button disabled>{{ __('Install App') }}</button>
            <a href="{{ $returnUrl }}" data-dk-install-return>{{ __('Back to dashboard') }}</a>
        </div>

        <p class="dk-install-manual" data-dk-install-manual hidden>
            {{ __('If the install dialog does not open, use your browser menu and choose Install app or Add to Home screen.') }}
        </p>
    </main>

    @php($pwaScriptVersion = config('app.version').'-'.filemtime(public_path('frontend/js/pwa.js')))
    <script src="{{ asset('frontend/js/pwa.js?v='.$pwaScriptVersion) }}" defer></script>
    <script>
        (function () {
            "use strict";

            var page = document.querySelector("[data-dk-install-page]");
            var button = document.querySelector("[data-dk-install-button]");
            var status = document.querySelector("[data-dk-install-status]");
            var manual = document.querySelector("[data-dk-install-manual]");
            var returnUrl = page ? page.getAttribute("data-dk-return-url") : "/user/dashboard";

            function setStatus(message) {
                if (status && message) {
                    status.textContent = message;
                }
            }

            function setReady() {
                if (button) {
                    button.disabled = false;
                }

                setStatus("Ready. Tap Install App to open the secure browser install dialog.");
            }

            function setManual() {
                if (button) {
                    button.disabled = false;
                }

                if (manual) {
                    manual.hidden = false;
                }

                setStatus("The browser did not expose the native install dialog on this page.");
            }

            function canInstall() {
                return window.DKPwa && window.DKPwa.canInstall();
            }

            function returnToDashboard() {
                window.location.replace(returnUrl || "/user/dashboard");
            }

            window.addEventListener("dk:pwa-install-available", setReady);
            window.addEventListener("dk:pwa-installed", returnToDashboard);

            if (button) {
                button.addEventListener("click", function () {
                    if (!canInstall()) {
                        setManual();

                        return;
                    }

                    button.disabled = true;
                    setStatus("Opening the install dialog...");

                    window.DKPwa.install().then(function (installed) {
                        if (installed) {
                            setStatus("Installed. Returning to your dashboard...");
                            window.setTimeout(returnToDashboard, 600);

                            return;
                        }

                        setManual();
                    });
                });
            }

            window.addEventListener("load", function () {
                window.setTimeout(function () {
                    if (canInstall()) {
                        setReady();

                        return;
                    }

                    setManual();
                }, 3000);
            });
        })();
    </script>
</body>
</html>
