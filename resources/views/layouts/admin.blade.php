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
    <script>
        function notifBell() {
            return {
                open: false,
                unread: {{ (int) ($navUnread ?? 0) }},
                items: @js(($navNotifications ?? collect())->map(fn ($n) => [
                    'id' => $n->id,
                    'title' => $n->title,
                    'body' => $n->body,
                    'time' => $n->created_at?->diffForHumans(),
                    'read' => ! is_null($n->read_at),
                    'icon' => \App\Models\StaffNotification::iconFor($n->type),
                    'color' => \App\Models\StaffNotification::colorFor($n->type),
                ])->values()->all() ?? []),
                async toggle() {
                    this.open = !this.open;
                    if (this.open) await this.refresh(false);
                },
                async refresh(markRead = false) {
                    try {
                        const res = await fetch('{{ route('admin.notifications.latest') }}', { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        if (!data.status) return;
                        const icons = { reservation: 'ri-calendar-check-line', order: 'ri-receipt-2-line', system: 'ri-notification-3-line' };
                        const colors = { reservation: 'bg-amber-100 text-amber-600', order: 'bg-sky-100 text-sky-600', system: 'bg-slate-100 text-slate-500' };
                        this.unread = data.unread;
                        this.items = data.items.map((n) => ({ ...n, icon: icons[n.type] || icons.system, color: colors[n.type] || colors.system }));
                        if (markRead) await this.readAll();
                    } catch (e) { /* offline: keep cached */ }
                },
                async readAll() {
                    try {
                        await fetch('{{ route('admin.notifications.readAll') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                            },
                        });
                        this.unread = 0;
                        this.items = this.items.map((n) => ({ ...n, read: true }));
                    } catch (e) { /* ignore */ }
                },
                prepend(e) {
                    this.unread++;
                    this.items.unshift({
                        id: 'live-' + Date.now(),
                        title: `New reservation: ${e.customer_name}`,
                        body: `${e.guest_count} guests${e.table_name ? ' · ' + e.table_name : ''}`,
                        time: 'just now',
                        read: false,
                        icon: 'ri-calendar-check-line',
                        color: 'bg-amber-100 text-amber-600',
                    });
                    this.items = this.items.slice(0, 10);
                },
            };
        }
    </script>
    @stack('footer')
    @auth
    <script>
        // Global live reservation ping for every staff screen.
        (function () {
            if (!window.Echo) return;
            // One friendly notice when the socket server is down (instead of console spam).
            try {
                window.Echo.connector?.pusher?.connection?.bind('unavailable', () => {
                    window.toast?.warning('Realtime offline — run "php artisan reverb:start" for live updates.', 8000);
                });
            } catch (e) { /* pusher internals vary by version */ }
            const ownerId = {{ (int) panel_owner_id() }};
            window.Echo.private(`admin.${ownerId}.reservations`)
                .listen('.ReservationPlaced', (e) => {
                    window.dispatchEvent(new CustomEvent('notif-live', { detail: e }));
                    const when = e.reservation_time ? new Date(e.reservation_time).toLocaleString() : '';
                    window.toast?.warning(`New reservation: ${e.customer_name} (${e.guest_count} guests)${e.table_name ? ' · ' + e.table_name : ''}${when ? ' · ' + when : ''}`, 8000);
                    // If staff is looking at the reservations list, refresh it.
                    if (window.location.pathname.includes('/reservations')) {
                        setTimeout(() => window.location.reload(), 1500);
                    }
                });
        })();
    </script>
    @endauth
</body>
</html>

