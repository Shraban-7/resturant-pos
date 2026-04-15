<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title') &middot; POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    @stack('header')
    @stack('styles')
    <style>
        .item-card { cursor: pointer; }
        .item-card .card-body { padding: 0.5rem 0.75rem; }
    </style>
</head>
<body class="min-h-screen bg-slate-50">
    <div class="w-full px-3 py-2" id="content">
        <x-flash-message />
        @yield('content')
    </div>
    @stack('footer')
</body>
</html>
