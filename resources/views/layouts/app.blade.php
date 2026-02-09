<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts (Vite: Bootstrap 5 CSS+JS imported) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
        <div class="min-vh-100 bg-light">
            @include('layouts.navigation')

            <!-- Alert Container (AJAX) -->
            <div id="ajax-alert-container" class="position-fixed top-0 end-0 p-4 z-3" style="max-width: 420px;"></div>

            <!-- Page Content -->
            <main class="container py-4">
                @yield('content')
            </main>
        </div>
    </body>
</html>
