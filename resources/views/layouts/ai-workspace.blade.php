<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" sizes="16x16" href="{{ companyOrGlobalSetting()->favicon_url }}">

    <title>{{ is_array(__($pageTitle ?? 'app.menu.aiWorkspace')) ? $pageTitle ?? 'app.menu.aiWorkspace' : __($pageTitle ?? 'app.menu.aiWorkspace') }}</title>

    @stack('styles')

    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
        }

        .ai-workspace-page,
        .ai-workspace-page__root {
            width: 100%;
            min-height: 100vh;
        }
    </style>
</head>

<body class="ai-workspace-body">
    @yield('content')

    @stack('scripts')
</body>

</html>
