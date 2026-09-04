<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $business->name ?? config('app.name') }} | Order & Reserve</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        window.toast = window.toast || (function () {
            function box() {
                let el = document.getElementById('toast-container');
                if (!el) {
                    el = document.createElement('div');
                    el.id = 'toast-container';
                    el.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;max-width:min(92vw,24rem);';
                    document.body.appendChild(el);
                }
                return el;
            }
            function show(m, t) {
                const c = { success: '#059669', error: '#dc2626', warning: '#d97706', info: '#0284c7' };
                const b = box();
                while (b.children.length >= 4) b.firstElementChild?.remove();
                const el = document.createElement('div');
                el.style.cssText = 'background:#fff;border:1px solid #e2e8f0;border-left:4px solid ' + (c[t] || c.info) + ';border-radius:.75rem;box-shadow:0 12px 30px rgba(0,0,0,.18);padding:.7rem .8rem;font-size:.85rem;display:flex;gap:.5rem;align-items:center;';
                el.textContent = m;
                b.appendChild(el);
                setTimeout(() => el.remove(), 4500);
            }
            return { success: (m) => show(m, 'success'), error: (m) => show(m, 'error'), warning: (m) => show(m, 'warning'), info: (m) => show(m, 'info') };
        })();
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-bg { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { scrollbar-width: none; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800" x-data="storefront()" x-cloak>

<!-- Navbar -->
<header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-3">
        <a href="{{ route('storefront.index') }}" class="flex items-center gap-2 font-bold text-slate-900">
            <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-orange-600 text-white text-lg"><i class="ri-restaurant-2-line"></i></span>
            <span class="truncate">{{ $business->name ?? config('app.name') }}</span>
        </a>
        <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
            <a href="#menu" class="px-3 py-2 text-slate-600 hover:text-orange-600">Menu</a>
            <a href="#popular" class="px-3 py-2 text-slate-600 hover:text-orange-600">Popular</a>
            <a href="#branches" class="px-3 py-2 text-slate-600 hover:text-orange-600">Branches</a>
            <a href="{{ route('login') }}" class="px-3 py-2 text-slate-600 hover:text-orange-600">Staff Login</a>
        </nav>
        <a href="#reservation" class="bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold px-4 py-2 rounded-full transition">
            <i class="ri-calendar-check-line mr-1"></i>Reserve Table
        </a>
    </div>
</header>

<!-- Hero -->
<section class="hero-bg text-white">
    <div class="max-w-7xl mx-auto px-4 py-12 md:py-20 grid md:grid-cols-2 gap-8 items-center">
        <div>
            <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-amber-300 bg-white/10 rounded-full px-3 py-1">
                <i class="ri-map-pin-line"></i>{{ $branches->first()?->address ?? 'Dine-in • Takeaway' }}
            </p>
            <h1 class="text-3xl md:text-5xl font-extrabold leading-tight mt-4">{{ $business->name ?? config('app.name') }}</h1>
            <p class="text-slate-300 mt-3 max-w-md">Browse our menu, pick your favourites, then reserve a table in under a minute. Show up — we handle the rest.</p>
            <div class="flex flex-wrap gap-3 mt-6">
                <a href="#menu" class="bg-orange-600 hover:bg-orange-500 text-white font-semibold px-5 py-2.5 rounded-full text-sm transition">Browse Menu</a>
                <a href="#reservation" class="bg-white/10 hover:bg-white/20 border border-white/20 font-semibold px-5 py-2.5 rounded-full text-sm transition">Reserve a Table</a>
            </div>
            <div class="flex gap-6 mt-8 text-sm">
                <div><p class="text-2xl font-bold">{{ $categories->sum(fn ($c) => $c->products->count()) }}</p><p class="text-slate-400">Dishes</p></div>
                <div><p class="text-2xl font-bold">{{ $branches->count() }}</p><p class="text-slate-400">Branches</p></div>
                <div><p class="text-2xl font-bold">{{ $tables->count() }}</p><p class="text-slate-400">Free tables</p></div>
            </div>
        </div>
        <div class="hidden md:grid grid-cols-2 gap-3">
            @foreach ($popular->take(4) as $item)
                <div class="bg-white/10 border border-white/10 rounded-2xl p-3 backdrop-blur">
                    @if ($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="h-28 w-full object-cover rounded-xl" loading="lazy">
                    @else
                        <div class="h-28 w-full rounded-xl bg-white/10 flex items-center justify-center text-3xl"><i class="ri-bowl-line text-amber-300"></i></div>
                    @endif
                    <p class="font-semibold text-sm mt-2 truncate">{{ $item->name }}</p>
                    <p class="text-amber-300 font-bold text-sm">{{ money($item->selling_price) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Menu -->
<section id="menu" class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-xl md:text-2xl font-bold text-slate-900">Our Menu</h2>
            <p class="text-sm text-slate-500">Dine-in ordering happens by scanning the QR on your table.</p>
        </div>
        <div class="relative w-full sm:w-64">
            <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input x-model="query" type="text" placeholder="Search dishes..." class="w-full border border-slate-200 rounded-full pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
        </div>
    </div>

    <div class="flex gap-2 overflow-x-auto no-scrollbar py-4">
        <button @click="category = ''" :class="category === '' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200'" class="shrink-0 text-sm font-medium px-4 py-1.5 rounded-full transition">All</button>
        @foreach ($categories as $cat)
            <button @click="category = '{{ $cat->id }}'" :class="category === '{{ $cat->id }}' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200'" class="shrink-0 text-sm font-medium px-4 py-1.5 rounded-full transition">{{ $cat->name }}</button>
        @endforeach
    </div>

    @forelse ($categories as $cat)
        <div x-show="(category === '' || category === '{{ $cat->id }}')" class="mb-8">
            <h3 class="font-bold text-slate-900 flex items-center gap-2"><span class="h-5 w-1 rounded bg-orange-600 inline-block"></span>{{ $cat->name }}</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4 mt-3">
                @foreach ($cat->products as $product)
                    <div x-show="'{{ strtolower($product->name) }}'.includes(query.toLowerCase())"
                         class="bg-white border border-slate-200/70 rounded-2xl overflow-hidden hover:shadow-md hover:border-orange-200 transition">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-32 md:h-40 w-full object-cover" loading="lazy">
                        @else
                            <div class="h-32 md:h-40 w-full bg-slate-100 flex items-center justify-center text-4xl text-slate-300"><i class="ri-bowl-line"></i></div>
                        @endif
                        <div class="p-3">
                            <p class="font-semibold text-sm text-slate-900 truncate">{{ $product->name }}</p>
                            <div class="flex items-center justify-between mt-1.5">
                                <p class="font-bold text-orange-700 text-sm">{{ money($product->selling_price) }}</p>
                                @if (($product->stock_in - $product->stock_out) > 0)
                                    <span class="text-[11px] font-medium text-emerald-700 bg-emerald-50 rounded-full px-2 py-0.5">Available</span>
                                @else
                                    <span class="text-[11px] font-medium text-red-700 bg-red-50 rounded-full px-2 py-0.5">Sold out</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-10 text-center text-slate-500">
            <i class="ri-bowl-line text-4xl"></i>
            <p class="font-semibold mt-2">Menu coming soon</p>
        </div>
    @endforelse
</section>

<!-- Popular strip -->
@if ($popular->isNotEmpty())
<section id="popular" class="bg-white border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h2 class="text-xl font-bold text-slate-900">Most loved right now</h2>
        <div class="flex gap-3 overflow-x-auto no-scrollbar mt-4 pb-1">
            @foreach ($popular as $item)
                <div class="shrink-0 w-44 bg-slate-50 border border-slate-200 rounded-2xl p-3">
                    <p class="font-semibold text-sm truncate">{{ $item->name }}</p>
                    <p class="text-orange-700 font-bold text-sm mt-0.5">{{ money($item->selling_price) }}</p>
                    <p class="text-[11px] text-slate-500">{{ $item->stock_out }} sold</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Reservation -->
<section id="reservation" class="max-w-7xl mx-auto px-4 py-10">
    <div class="grid lg:grid-cols-5 gap-6">
        <div class="lg:col-span-2">
            <h2 class="text-xl md:text-2xl font-bold text-slate-900">Reserve a table</h2>
            <p class="text-sm text-slate-500 mt-1">Pick a branch, table and time. Requests start as <span class="font-semibold">pending</span> — our staff confirms shortly.</p>
            <ul class="mt-4 space-y-2 text-sm text-slate-600">
                <li class="flex gap-2"><i class="ri-check-line text-emerald-600 mt-0.5"></i>Free — no advance payment</li>
                <li class="flex gap-2"><i class="ri-check-line text-emerald-600 mt-0.5"></i>Confirmation by phone</li>
                <li class="flex gap-2"><i class="ri-check-line text-emerald-600 mt-0.5"></i>Order at the table by scanning its QR</li>
            </ul>
            @if ($business?->phone)
                <a href="tel:{{ $business->phone }}" class="inline-flex items-center gap-2 mt-4 text-sm font-semibold text-orange-700"><i class="ri-phone-line"></i>{{ $business->phone }}</a>
            @endif
        </div>
        <form id="reserve-form" class="lg:col-span-3 bg-white border border-slate-200 rounded-2xl p-5 md:p-6 grid sm:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="text-xs font-semibold text-slate-600">Your name *</label>
                <input name="customer_name" required class="mt-1 w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Full name">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600">Phone *</label>
                <input name="customer_phone" required class="mt-1 w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="01XXXXXXXXX">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600">Guests *</label>
                <input name="guest_count" type="number" min="1" max="100" value="2" required class="mt-1 w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600">Date & time *</label>
                <input name="reservation_time" type="datetime-local" required class="mt-1 w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            @if ($branches->isNotEmpty())
            <div>
                <label class="text-xs font-semibold text-slate-600">Branch</label>
                <select id="branch-select" name="branch_id" class="mt-1 w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Any branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="text-xs font-semibold text-slate-600">Table * (free tables only)</label>
                <select id="table-select" name="table_id" required class="mt-1 w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @forelse ($tables as $table)
                        <option value="{{ $table->id }}" data-branch="{{ $table->branch_id }}">{{ $table->name }}</option>
                    @empty
                        <option value="" disabled>No free tables right now</option>
                    @endforelse
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="text-xs font-semibold text-slate-600">Notes (optional)</label>
                <textarea name="notes" rows="2" class="mt-1 w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Birthday, window seat..."></textarea>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" id="reserve-btn" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2.5 rounded-xl text-sm transition disabled:opacity-60">
                    Request Reservation
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Branches -->
@if ($branches->isNotEmpty())
<section id="branches" class="max-w-7xl mx-auto px-4 pb-12">
    <h2 class="text-xl font-bold text-slate-900">Find us</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
        @foreach ($branches as $branch)
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <p class="font-bold text-slate-900 flex items-center gap-2"><i class="ri-store-2-line text-orange-600"></i>{{ $branch->name }}</p>
                @if ($branch->address)<p class="text-sm text-slate-500 mt-1 flex gap-1.5"><i class="ri-map-pin-line mt-0.5"></i>{{ $branch->address }}</p>@endif
                @if ($branch->phone)<a href="tel:{{ $branch->phone }}" class="text-sm text-orange-700 font-semibold mt-1 inline-block"><i class="ri-phone-line"></i> {{ $branch->phone }}</a>@endif
                @if ($branch->is_default)<span class="inline-block mt-2 text-[11px] font-semibold bg-amber-50 text-amber-700 rounded-full px-2 py-0.5">Head branch</span>@endif
            </div>
        @endforeach
    </div>
</section>
@endif

<footer class="bg-slate-900 text-slate-400">
    <div class="max-w-7xl mx-auto px-4 py-8 flex flex-col md:flex-row items-center justify-between gap-3 text-sm">
        <p>© {{ date('Y') }} {{ $business->name ?? config('app.name') }}. All rights reserved.</p>
        <div class="flex gap-4">
            <a href="#menu" class="hover:text-white">Menu</a>
            <a href="#reservation" class="hover:text-white">Reserve</a>
            <a href="{{ route('login') }}" class="hover:text-white">Staff Login</a>
        </div>
    </div>
</footer>

<script>
function storefront() {
    return { query: '', category: '' };
}
(function () {
    const branchSel = document.getElementById('branch-select');
    const tableSel = document.getElementById('table-select');
    if (branchSel && tableSel) {
        const all = Array.from(tableSel.options).map(o => ({ value: o.value, text: o.text, branch: o.dataset.branch }));
        branchSel.addEventListener('change', () => {
            const b = branchSel.value;
            tableSel.innerHTML = '';
            all.filter(o => !b || o.branch === b).forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value; opt.textContent = o.text;
                tableSel.appendChild(opt);
            });
            if (!tableSel.options.length) {
                const opt = document.createElement('option');
                opt.disabled = true; opt.textContent = 'No free tables at this branch';
                tableSel.appendChild(opt);
            }
        });
    }
    const form = document.getElementById('reserve-form');
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('reserve-btn');
        btn.disabled = true;
        try {
            const res = await fetch('{{ route('storefront.reserve') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });
            const data = await res.json();
            if (!res.ok || !data.status) {
                const msg = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Reservation failed.';
                throw new Error(msg);
            }
            window.toast?.success(data.message);
            form.reset();
        } catch (err) {
            window.toast?.error(err.message || 'Reservation failed.');
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
</body>
</html>
