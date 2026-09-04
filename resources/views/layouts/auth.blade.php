<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') &middot; {{ config('app.name', env('APP_NAME')) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center p-4">
    <main class="w-full max-w-md">
        <x-flash-message />
        @yield('content')
        <p class="text-center text-xs text-slate-400 mt-6">{{ config('app.name', env('APP_NAME')) }} · Restaurant POS</p>
    </main>
</body>
</html>
