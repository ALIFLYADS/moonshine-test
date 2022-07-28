<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config("moonshine.title") }}</title>

        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('vendor/moonshine/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vendor/moonshine/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vendor/moonshine/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ asset('vendor/moonshine/site.webmanifest') }}">
        <link rel="mask-icon" href="{{ asset('vendor/moonshine/safari-pinned-tab.svg') }}" color="#7665FF">
        <meta name="msapplication-TileColor" content="#7665FF">
        <meta name="theme-color" content="#7665FF">

        @vite(['resources/css/app.css', 'resources/js/app.js'], 'vendor/moonshine')

		@yield('after-styles')
    </head>
    <body>
        <div>
            @include('moonshine::shared.alert')

            @yield('content')
        </div>

		@yield('after-scripts')
    </body>
</html>
