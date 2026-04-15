<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    @stack('header')
    @stack('styles')
</head>
<body x-data="{ sidebarOpen: false }" x-cloak>
    <div class="app-shell">
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="app-sidebar-overlay"></div>

        <aside class="app-sidebar" :class="{ 'open': sidebarOpen }">
            @include('layouts.partials.admin.sidebar')
        </aside>

        <div class="app-main">
            @hasSection('full_page')
            @else
            <header class="app-topbar">
                @include('layouts.partials.admin.navbar')
            </header>
            @endif

            <main class="flex-1 flex flex-col min-w-0">
                <div class="app-content">
                    <x-flash-message />
                    @yield('content')
                    @yield('full_page')
                </div>
                @include('layouts.partials.admin.footer')
            </main>
        </div>
    </div>
    @stack('footer')
</body>
</html>
