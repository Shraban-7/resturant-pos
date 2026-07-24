<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Status · {{ $table->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --ink: #1c1917;
            --muted: #78716c;
            --paper: #fffdf8;
            --accent: #b45309;
            --ok: #15803d;
            --track: #e7e5e4;
        }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'DM Sans', sans-serif;
            background:
                radial-gradient(ellipse at top, rgba(180, 83, 9, 0.08), transparent 55%),
                linear-gradient(180deg, #fafaf9 0%, #f5f5f4 100%);
            color: var(--ink);
        }
        .wrap { max-width: 28rem; margin: 0 auto; padding: 1.5rem 1.25rem 3rem; }
        .brand {
            font-family: 'Fraunces', serif;
            font-size: 1.35rem;
            font-weight: 600;
            margin: 0 0 0.25rem;
        }
        .meta { color: var(--muted); font-size: 0.875rem; margin-bottom: 1.75rem; }
        .card {
            background: var(--paper);
            border: 1px solid var(--track);
            border-radius: 1.25rem;
            padding: 1.5rem;
            box-shadow: 0 16px 40px rgba(28, 25, 23, 0.06);
        }
        .order-id {
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent);
            font-weight: 700;
            margin-bottom: 0.35rem;
        }
        .live {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            color: var(--muted);
        }
        .dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            background: #f59e0b;
        }
        .dot.on { background: #22c55e; }
        .steps { margin: 1.75rem 0 0; padding: 0; list-style: none; }
        .step {
            display: grid;
            grid-template-columns: 1.75rem 1fr;
            gap: 0.85rem;
            position: relative;
            padding-bottom: 1.35rem;
        }
        .step:last-child { padding-bottom: 0; }
        .step:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 0.8rem;
            top: 1.6rem;
            bottom: 0.15rem;
            width: 2px;
            background: var(--track);
            transform: translateX(-50%);
        }
        .step.done:not(:last-child)::before { background: var(--ok); }
        .step.active:not(:last-child)::before {
            background: linear-gradient(180deg, var(--ok), var(--track));
        }
        .bubble {
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 999px;
            border: 2px solid var(--track);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            color: var(--muted);
            z-index: 1;
        }
        .step.done .bubble { border-color: var(--ok); background: var(--ok); color: #fff; }
        .step.active .bubble { border-color: var(--accent); color: var(--accent); box-shadow: 0 0 0 4px rgba(180, 83, 9, 0.12); }
        .step-label { font-weight: 600; font-size: 0.95rem; padding-top: 0.15rem; }
        .step-sub { font-size: 0.8rem; color: var(--muted); margin-top: 0.15rem; }
        .items { margin-top: 1.5rem; border-top: 1px solid var(--track); padding-top: 1.25rem; }
        .item { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.9rem; padding: 0.4rem 0; }
        .item-name { font-weight: 500; }
        .item-mod { font-size: 0.75rem; color: var(--muted); }
        .empty { text-align: center; padding: 2rem 0.5rem; color: var(--muted); }
        .actions { margin-top: 1.25rem; display: grid; gap: 0.6rem; }
        .btn-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            text-decoration: none;
            border-radius: 0.75rem;
            padding: 0.85rem 1rem;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-primary { background: var(--ink); color: #fff; }
        .btn-ghost { background: #fff; color: var(--ink); border: 1px solid var(--track); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body
    x-data="orderTracker(@js([
        'token' => $token,
        'orderId' => $sale?->order_id,
        'status' => $status,
        'steps' => collect($steps)->pluck('key')->values(),
    ]))"
    x-cloak
>
    <div class="wrap">
        <h1 class="brand">Order tracker</h1>
        <p class="meta">{{ $table->name }} · watch your meal from kitchen to table</p>

        <div class="card">
            @if($sale)
                <div class="order-id">Order #{{ $sale->order_id }}</div>
                <div class="live">
                    <span class="dot" :class="connected && 'on'"></span>
                    <span x-text="connected ? 'Live updates on' : 'Connecting…'"></span>
                </div>

                <ul class="steps">
                    @foreach($steps as $index => $step)
                        <li class="step"
                            :class="{
                                done: stepIndex('{{ $step['key'] }}') < currentIndex,
                                active: stepIndex('{{ $step['key'] }}') === currentIndex
                            }">
                            <div class="bubble">
                                <template x-if="stepIndex('{{ $step['key'] }}') < currentIndex"><span>✓</span></template>
                                <template x-if="stepIndex('{{ $step['key'] }}') >= currentIndex"><span>{{ $index + 1 }}</span></template>
                            </div>
                            <div>
                                <div class="step-label">{{ $step['label'] }}</div>
                                <div class="step-sub" x-show="stepIndex('{{ $step['key'] }}') === currentIndex">Current step</div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="items">
                    @foreach($sale->items as $item)
                        <div class="item">
                            <div>
                                <div class="item-name">{{ $item->quantity }}× {{ $item->item_name }}</div>
                                @php
                                    $mods = collect($item->modifiers_json ?? [])->pluck('name')->filter();
                                @endphp
                                @if($mods->isNotEmpty())
                                    <div class="item-mod">{{ $mods->join(', ') }}</div>
                                @endif
                            </div>
                            <div>{{ money($item->total_price) }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">
                    <i class="ri-restaurant-2-line text-4xl opacity-40"></i>
                    <p class="mt-3 font-medium text-slate-700">No order found for this table yet.</p>
                    <p class="text-sm">Place an order from the digital menu, then return here to track it.</p>
                </div>
            @endif

            <div class="actions">
                <a class="btn-link btn-primary" href="{{ route('menu.index', $table) }}">
                    <i class="ri-restaurant-line"></i> Back to menu
                </a>
            </div>
        </div>
    </div>

    <script>
        function orderTracker(cfg) {
            const rank = { pending: 0, preparing: 1, ready: 2, served: 3, cancelled: -1 };
            return {
                token: cfg.token,
                orderId: cfg.orderId,
                status: cfg.status || 'pending',
                stepKeys: cfg.steps || ['received', 'preparing', 'ready', 'served'],
                connected: false,
                get currentIndex() {
                    const map = { pending: 0, preparing: 1, ready: 2, served: 3 };
                    return map[this.status] ?? 0;
                },
                stepIndex(key) {
                    return this.stepKeys.indexOf(key);
                },
                init() {
                    if (!window.Echo || !this.token) return;
                    window.Echo.channel(`table.${this.token}`)
                        .listen('.OrderPlaced', (e) => {
                            if (this.orderId && e.order_id && e.order_id !== this.orderId) return;
                            this.status = e.status || 'pending';
                            this.connected = true;
                        })
                        .listen('.KitchenStatusUpdated', (e) => {
                            if (this.orderId && e.order_id && e.order_id !== this.orderId) return;
                            if (rank[e.status] === undefined) return;
                            this.status = e.status;
                            this.connected = true;
                        });
                    this.connected = true;
                },
            };
        }
    </script>
</body>
</html>
