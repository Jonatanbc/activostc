<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ Helper::determineLanguageDirection() }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="baseUrl" content="{{ url('/') }}/">
    <title>@yield('title')</title>

    <link rel="stylesheet" href="{{ url(mix('css/dist/all.css')) }}">
    <script nonce="{{ csrf_token() }}">
        window.snipeit = { settings: { "per_page": 50 } };
    </script>
    @stack('css')
    <style>
        body { background: #fff; padding: 8px 4px; }
        /* This page renders inside a modal iframe: no page chrome needed. */
    </style>
</head>
<body class="modal-embed">
    @yield('content')

    <script src="{{ url(mix('js/dist/all.js')) }}" nonce="{{ csrf_token() }}"></script>
    @yield('moar_scripts')
    @stack('js')
</body>
</html>
