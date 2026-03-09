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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-indigo-600">
        <div class="min-h-screen flex flex-col">
            <!-- Main content (form area) -->
            <div class="flex flex-1 items-center justify-center">
                <div class="w-full bg-white/90 backdrop-blur-lg rounded-2xl">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
