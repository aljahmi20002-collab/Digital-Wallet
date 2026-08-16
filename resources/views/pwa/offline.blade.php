<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ $themeColor }}">
    <title>{{ __('Offline') }} | {{ $siteTitle }}</title>
    <style>
        :root {
            color-scheme: light;
            --dk-primary: {{ $themeColor }};
            --dk-bg: {{ $backgroundColor }};
            --dk-card: #ffffff;
            --dk-text: #172033;
            --dk-muted: #64748b;
            --dk-line: #e3e7ff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background: var(--dk-bg);
            color: var(--dk-text);
            font-family: "Plus Jakarta Sans", Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .dk-offline-page {
            width: min(100%, 420px);
            border: 1px solid var(--dk-line);
            border-radius: 18px;
            padding: 28px;
            background: var(--dk-card);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
            text-align: center;
        }

        .dk-offline-mark {
            width: 64px;
            height: 64px;
            margin: 0 auto 18px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--dk-primary), #22bdff);
            color: #ffffff;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .dk-offline-mark img {
            width: 100%;
            height: 100%;
            border-radius: inherit;
            object-fit: cover;
        }

        h1 {
            margin: 0;
            font-size: 22px;
            line-height: 1.25;
            letter-spacing: 0;
        }

        p {
            margin: 10px 0 22px;
            color: var(--dk-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        button {
            width: 100%;
            min-height: 46px;
            border: 0;
            border-radius: 12px;
            background: var(--dk-primary);
            color: #ffffff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        button:active {
            transform: translateY(1px);
        }
    </style>
</head>
<body>
    <main class="dk-offline-page">
        <div class="dk-offline-mark" aria-hidden="true">
            <img src="{{ $iconUrl }}" alt="" loading="lazy">
        </div>
        <h1>{{ __('You are offline') }}</h1>
        <p>{{ __($offlineMessage ?? 'A live connection is required for balances, payments, and transactions. Please reconnect and try again.') }}</p>
        <button type="button" onclick="window.location.reload()">{{ __('Try again') }}</button>
    </main>
</body>
</html>
