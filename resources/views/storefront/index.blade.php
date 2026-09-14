<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $business->name ?? config('app.name') }} | {{ __('storefront.home') }} & Menu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        window.toast = window.toast || (function() {
            function box() {
                let el = document.getElementById('toast-container');
                if (!el) {
                    el = document.createElement('div');
                    el.id = 'toast-container';
                    el.style.cssText =
                        'position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;max-width:min(92vw,24rem);';
                    document.body.appendChild(el);
                }
                return el;
            }

            function show(m, t) {
                const c = {
                    success: '#059669',
                    error: '#dc2626',
                    warning: '#d97706',
                    info: '#0284c7'
                };
                const b = box();
                while (b.children.length >= 4) b.firstElementChild?.remove();
                const el = document.createElement('div');
                el.style.cssText = 'background:#fff;border:1px solid #e2e8f0;border-left:4px solid ' + (c[t] || c
                        .info) +
                    ';border-radius:.75rem;box-shadow:0 12px 30px rgba(0,0,0,.18);padding:.7rem .8rem;font-size:.85rem;display:flex;gap:.5rem;align-items:center;';
                el.textContent = m;
                b.appendChild(el);
                setTimeout(() => el.remove(), 4500);
            }
            return {
                success: (m) => show(m, 'success'),
                error: (m) => show(m, 'error'),
                warning: (m) => show(m, 'warning'),
                info: (m) => show(m, 'info')
            };
        })();
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .hero-bg {
            background: radial-gradient(circle at 20% 30%, #1e293b, #0f172a 90%);
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            scrollbar-width: none;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body id="top" class="bg-slate-50 text-slate-800 antialiased" style="scroll-behavior:smooth"
    x-data="storefront()" x-cloak>

    @if (($store['announcement_enabled'] ?? '0') === '1' && !empty($store['announcement_text']))
        <div class="bg-orange-600 text-white text-center text-xs sm:text-sm font-medium px-4 py-2">
            <i class="ri-megaphone-line mr-1"></i>{{ $store['announcement_text'] }}
        </div>
    @endif

    <!-- Navbar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-3">
            <!-- Logo -->
            <a href="{{ route('storefront.index') }}"
                class="flex items-center gap-2.5 font-bold text-slate-900 shrink-0">
                @if (store_logo_url())
                    <img src="{{ store_logo_url() }}" alt="{{ store_name() }}"
                        class="h-9 w-9 rounded-xl object-cover shrink-0">
                @else
                    <span
                        class="flex items-center justify-center h-9 w-9 rounded-xl bg-orange-600 text-white text-lg"><i
                            class="ri-restaurant-2-line"></i></span>
                @endif
                <span
                    class="truncate max-w-[150px] sm:max-w-none text-base sm:text-lg">{{ $business->name ?? store_name() }}</span>
            </a>

            <!-- Desktop Navigation (All 9 sections accessible) -->
            <nav class="hidden lg:flex items-center gap-1 text-sm font-medium text-slate-600">
                <a href="#top"
                    class="px-2.5 py-1.5 rounded-lg hover:text-orange-600 hover:bg-orange-50 transition">{{ __('storefront.home') }}</a>
                <a href="#menu"
                    class="px-2.5 py-1.5 rounded-lg hover:text-orange-600 hover:bg-orange-50 transition">{{ __('storefront.menu') }}</a>
                <a href="#offers"
                    class="px-2.5 py-1.5 rounded-lg hover:text-orange-600 hover:bg-orange-50 transition flex items-center gap-1">
                    <i class="ri-gift-line text-orange-500"></i>{{ __('storefront.offers') }}
                </a>
                <a href="#order-online"
                    class="px-2.5 py-1.5 rounded-lg hover:text-orange-600 hover:bg-orange-50 transition">{{ __('storefront.order_online') }}</a>
                <a href="#gallery"
                    class="px-2.5 py-1.5 rounded-lg hover:text-orange-600 hover:bg-orange-50 transition">{{ __('storefront.gallery') }}</a>
                @if (($store['show_branches'] ?? '1') === '1')
                    <a href="#branches"
                        class="px-2.5 py-1.5 rounded-lg hover:text-orange-600 hover:bg-orange-50 transition">{{ __('storefront.branches') }}</a>
                @endif
                <a href="#contact"
                    class="px-2.5 py-1.5 rounded-lg hover:text-orange-600 hover:bg-orange-50 transition">{{ __('storefront.contact') }}</a>
                <a href="{{ route('login') }}"
                    class="px-2.5 py-1.5 text-slate-500 hover:text-slate-800 transition">{{ __('storefront.staff_login') }}</a>
            </nav>

            <!-- Right CTA & Mobile Toggle -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Language switcher -->
                <span class="flex items-center rounded-full border border-slate-200 overflow-hidden text-xs font-bold">
                    <a href="{{ route('lang.switch', 'en') }}"
                        class="px-2.5 py-1.5 {{ app()->getLocale() === 'en' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-900' }}">EN</a>
                    <a href="{{ route('lang.switch', 'bn') }}"
                        class="px-2.5 py-1.5 {{ app()->getLocale() === 'bn' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-900' }}">বাং</a>
                </span>

                <!-- Reserve Button -->
                <a href="#reservation"
                    class="hidden sm:inline-flex items-center bg-orange-600 hover:bg-orange-700 text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-full transition shadow-sm hover:shadow">
                    <i class="ri-calendar-check-line mr-1.5"></i>{{ __('storefront.reserve_table') }}
                </a>

                <!-- Mobile Hamburger Button -->
                <button @click="mobileMenu = !mobileMenu" type="button"
                    class="lg:hidden p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl">
                    <i :class="mobileMenu ? 'ri-close-line' : 'ri-menu-line'" class="text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileMenu" @click.away="mobileMenu = false"
            class="lg:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-1 shadow-lg">
            <a @click="mobileMenu = false" href="#top"
                class="block px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-slate-50 hover:text-orange-600">{{ __('storefront.home') }}</a>
            <a @click="mobileMenu = false" href="#menu"
                class="block px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-slate-50 hover:text-orange-600">{{ __('storefront.menu') }}</a>
            <a @click="mobileMenu = false" href="#offers"
                class="block px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-slate-50 hover:text-orange-600 flex items-center gap-1.5">
                <i class="ri-gift-line text-orange-500"></i>{{ __('storefront.offers') }}
            </a>
            <a @click="mobileMenu = false" href="#order-online"
                class="block px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-slate-50 hover:text-orange-600">{{ __('storefront.order_online') }}</a>
            <a @click="mobileMenu = false" href="#reservation"
                class="block px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-slate-50 hover:text-orange-600">{{ __('storefront.reserve_table') }}</a>
            <a @click="mobileMenu = false" href="#gallery"
                class="block px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-slate-50 hover:text-orange-600">{{ __('storefront.gallery') }}</a>
            @if (($store['show_branches'] ?? '1') === '1')
                <a @click="mobileMenu = false" href="#branches"
                    class="block px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-slate-50 hover:text-orange-600">{{ __('storefront.branches') }}</a>
            @endif
            <a @click="mobileMenu = false" href="#contact"
                class="block px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-slate-50 hover:text-orange-600">{{ __('storefront.contact') }}</a>
            <a @click="mobileMenu = false" href="{{ route('login') }}"
                class="block px-3 py-2 rounded-lg font-medium text-slate-500 hover:bg-slate-50">{{ __('storefront.staff_login') }}</a>
        </div>
    </header>

    <!-- 1. HERO (Home) -->
    <section class="hero-bg text-white">
        <div class="max-w-7xl mx-auto px-4 py-12 md:py-20 grid md:grid-cols-2 gap-8 items-center">
            <div>
                <p
                    class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-amber-300 bg-white/10 rounded-full px-3 py-1">
                    <i class="ri-map-pin-line"></i>{{ $branches->first()?->address ?? 'Dine-in • Takeaway' }}
                </p>
                <h1 class="text-3xl md:text-5xl font-extrabold leading-tight mt-4">
                    {{ $store['hero_title'] ?: $business->name ?? config('app.name') }}</h1>
                <p class="text-slate-300 mt-3 max-w-md">{{ $store['hero_subtitle'] ?: __('storefront.hero_tagline') }}
                </p>
                @if (!empty($store['opening_hours']))
                    <p class="text-amber-300 text-sm font-medium mt-2"><i
                            class="ri-time-line mr-1"></i>{{ $store['opening_hours'] }}</p>
                @endif
                <div class="flex flex-wrap gap-3 mt-6">
                    <a href="#menu"
                        class="bg-orange-600 hover:bg-orange-500 text-white font-semibold px-5 py-2.5 rounded-full text-sm transition shadow-lg shadow-orange-600/20">{{ __('storefront.browse_menu') }}</a>
                    <a href="#offers"
                        class="bg-amber-500/20 hover:bg-amber-500/30 border border-amber-400/40 text-amber-300 font-semibold px-5 py-2.5 rounded-full text-sm transition">
                        <i class="ri-gift-line mr-1"></i>{{ __('storefront.offers') }}
                    </a>
                    <a href="#reservation"
                        class="bg-white/10 hover:bg-white/20 border border-white/20 font-semibold px-5 py-2.5 rounded-full text-sm transition">{{ __('storefront.reserve_table') }}</a>
                </div>
                <div class="flex gap-6 mt-8 text-sm border-t border-white/10 pt-6">
                    <div>
                        <p class="text-2xl font-bold">{{ $categories->sum(fn($c) => $c->products->count()) }}</p>
                        <p class="text-slate-400">{{ __('storefront.dishes') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $branches->count() }}</p>
                        <p class="text-slate-400">{{ __('storefront.branches') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $tables->count() }}</p>
                        <p class="text-slate-400">{{ __('storefront.free_tables') }}</p>
                    </div>
                </div>
            </div>
            <div class="hidden md:grid grid-cols-2 gap-3">
                @foreach ($popular->take(4) as $item)
                    <div @click="openProduct(@js([
    'id' => $item->id,
    'name' => $item->displayName(),
    'name_bn' => $item->name_bn,
    'price' => money($item->selling_price),
    'image' => $item->imageUrl(),
    'category' => $item->category?->name ?? 'Dish',
    'is_available' => $item->stock_in - $item->stock_out > 0,
    'is_buffet' => ($item->type instanceof \App\Enums\ProductType ? $item->type : \App\Enums\ProductType::tryFrom((string) $item->type)) === \App\Enums\ProductType::BUFFET,
    'meal_slots' => $item->meal_times?->map(fn($m) => $m instanceof \App\Enums\MealSlot ? $m->label() : ucfirst((string) $m))->all() ?? ['All day'],
]))"
                        class="bg-white/10 border border-white/10 rounded-2xl p-3 backdrop-blur cursor-pointer hover:bg-white/20 transition group">
                        <div class="relative overflow-hidden rounded-xl">
                            <img src="{{ $item->imageUrl() }}" alt="{{ $item->displayName() }}"
                                class="h-28 w-full object-cover group-hover:scale-105 transition duration-300"
                                loading="lazy">
                            <span
                                class="absolute top-2 right-2 bg-slate-900/80 backdrop-blur text-[10px] text-white px-2 py-0.5 rounded-full font-medium"><i
                                    class="ri-eye-line mr-1"></i>{{ __('storefront.view_details') }}</span>
                        </div>
                        <p class="font-semibold text-sm mt-2 truncate">{{ $item->displayName() }}</p>
                        <p class="text-amber-300 font-bold text-sm">{{ money($item->selling_price) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 2. MENU & 3. FOOD DETAILS -->
    <section id="menu" class="max-w-7xl mx-auto px-4 py-12">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-xl md:text-3xl font-bold text-slate-900">{{ __('storefront.our_menu') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ __('storefront.menu_note') }} · <span
                        class="text-orange-600 font-medium">Click any item for full food details</span></p>
            </div>
            <div class="relative w-full sm:w-72">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input x-model="query" type="text" placeholder="{{ __('storefront.search_dishes') }}"
                    class="w-full border border-slate-200 rounded-full pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white shadow-sm">
            </div>
        </div>

        @if ($currentSlot)
            <div
                class="flex items-center gap-2 mt-4 bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-2.5 text-sm">
                <span class="relative flex h-2.5 w-2.5"><span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-60"></span><span
                        class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-600"></span></span>
                <p class="text-emerald-800">
                    {{ __('storefront.serving_now', ['slot' => $mealSlots[$currentSlot]['label']]) }}
                    <button @click="slot = 'all'" x-show="slot !== 'all'"
                        class="underline font-semibold ml-1">{{ __('storefront.show_full_menu') }}</button>
                </p>
            </div>
        @endif

        <!-- Meal Slot Filter -->
        <div class="flex gap-2 overflow-x-auto no-scrollbar py-4">
            <button @click="slot = 'all'"
                :class="slot === 'all' ? 'bg-orange-600 text-white border-orange-600 shadow-sm' :
                    'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                class="shrink-0 text-sm font-medium px-4 py-1.5 rounded-full border transition">🕐
                {{ __('storefront.all_day') }}</button>
            @foreach ($mealSlots as $key => $meta)
                <button @click="slot = '{{ $key }}'"
                    :class="slot === '{{ $key }}' ? 'bg-orange-600 text-white border-orange-600 shadow-sm' :
                        'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                    class="shrink-0 text-sm font-medium px-4 py-1.5 rounded-full border transition">{{ $meta['label'] }}
                    ({{ human_slot_range($meta['from'], $meta['to']) }})</button>
            @endforeach
        </div>

        <!-- Category Tabs -->
        <div class="flex gap-2 overflow-x-auto no-scrollbar pb-4">
            <button @click="category = ''"
                :class="category === '' ? 'bg-slate-900 text-white shadow-sm' :
                    'bg-white text-slate-600 border border-slate-200 hover:border-slate-300'"
                class="shrink-0 text-sm font-medium px-4 py-1.5 rounded-full transition">{{ __('storefront.all_cuisines') }}</button>
            @foreach ($categories as $cat)
                <button @click="category = '{{ $cat->id }}'"
                    :class="category === '{{ $cat->id }}' ? 'bg-slate-900 text-white shadow-sm' :
                        'bg-white text-slate-600 border border-slate-200 hover:border-slate-300'"
                    class="shrink-0 text-sm font-medium px-4 py-1.5 rounded-full transition">{{ $cat->name }}</button>
            @endforeach
        </div>

        <!-- Dishes Grid -->
        @forelse ($categories as $cat)
            <div x-show="(category === '' || category === '{{ $cat->id }}')" class="mb-10">
                <h3 class="font-bold text-lg text-slate-900 flex items-center gap-2 mb-4">
                    <span class="h-5 w-1.5 rounded-full bg-orange-600 inline-block"></span>{{ $cat->name }}
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 md:gap-5">
                    @foreach ($cat->products as $product)
                        @php
                            $sfProdType =
                                $product->type instanceof \App\Enums\ProductType
                                    ? $product->type
                                    : \App\Enums\ProductType::tryFrom((string) $product->type);
                            $isAvailable = $product->stock_in - $product->stock_out > 0;
                            $slotsArr = $product->meal_times
                                ?->map(fn($m) => $m instanceof \App\Enums\MealSlot ? $m->label() : ucfirst((string) $m))
                                ->all() ?? ['All day'];
                        @endphp
                        <div x-show="'{{ strtolower($product->name) }}'.includes(query.toLowerCase()) && (slot === 'all' || {{ !$product->meal_times || $product->meal_times->isEmpty() ? 'true' : 'false' }} || @js($product->meal_times?->map(fn($m) => $m instanceof \App\Enums\MealSlot ? $m->value : (string) $m)->all() ?? []).includes(slot))"
                            @click="openProduct(@js([
    'id' => $product->id,
    'name' => $product->displayName(),
    'name_bn' => $product->name_bn,
    'price' => money($product->selling_price),
    'image' => $product->imageUrl(),
    'category' => $cat->name,
    'is_available' => $isAvailable,
    'is_buffet' => $sfProdType === \App\Enums\ProductType::BUFFET,
    'meal_slots' => $slotsArr,
]))"
                            class="bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-lg hover:border-orange-300 transition duration-200 cursor-pointer group flex flex-col">
                            <div class="relative overflow-hidden">
                                <img src="{{ $product->imageUrl() }}" alt="{{ $product->displayName() }}"
                                    class="h-36 md:h-44 w-full object-cover group-hover:scale-105 transition duration-300"
                                    loading="lazy">
                                <span
                                    class="absolute bottom-2 right-2 bg-white/90 backdrop-blur text-slate-800 text-[11px] font-semibold px-2 py-0.5 rounded-full opacity-0 group-hover:opacity-100 transition shadow">
                                    <i class="ri-search-eye-line mr-0.5"></i>{{ __('storefront.view_details') }}
                                </span>
                            </div>
                            <div class="p-3.5 flex flex-col flex-1">
                                <p
                                    class="font-bold text-sm text-slate-900 group-hover:text-orange-600 transition truncate">
                                    {{ $product->displayName() }}</p>
                                @if ($product->name_bn && app()->getLocale() !== 'bn')
                                    <p class="text-xs text-slate-400 truncate">{{ $product->name_bn }}</p>
                                @endif
                                <div class="mt-1.5 flex flex-wrap gap-1">
                                    @if (!$product->meal_times || $product->meal_times->isEmpty())
                                        <span
                                            class="text-[10px] font-semibold text-sky-700 bg-sky-50 rounded-full px-2 py-0.5">{{ __('storefront.all_day') }}</span>
                                    @else
                                        @foreach ($product->meal_times as $ms)
                                            <span
                                                class="text-[10px] font-semibold text-amber-700 bg-amber-50 rounded-full px-2 py-0.5">{{ $ms instanceof \App\Enums\MealSlot ? $ms->label() : ucfirst((string) $ms) }}</span>
                                        @endforeach
                                    @endif
                                    @if ($sfProdType === \App\Enums\ProductType::BUFFET)
                                        <span
                                            class="text-[10px] font-bold text-white bg-emerald-600 rounded-full px-2 py-0.5">BUFFET</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between mt-auto pt-3 border-t border-slate-100">
                                    <p class="font-extrabold text-orange-600 text-sm">
                                        {{ money($product->selling_price) }}@if ($sfProdType === \App\Enums\ProductType::BUFFET)
                                            <span class="font-normal text-xs text-slate-400">/person</span>
                                        @endif
                                    </p>
                                    @if ($isAvailable)
                                        <span
                                            class="text-[11px] font-medium text-emerald-700 bg-emerald-50 rounded-full px-2 py-0.5">{{ __('storefront.available') }}</span>
                                    @else
                                        <span
                                            class="text-[11px] font-medium text-red-700 bg-red-50 rounded-full px-2 py-0.5">{{ __('storefront.sold_out') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @empty
                <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-12 text-center text-slate-500">
                    <i class="ri-bowl-line text-5xl text-slate-300"></i>
                    <p class="font-semibold mt-3">{{ __('storefront.menu_coming_soon') }}</p>
                </div>
            @endforelse
        </section>

        <!-- 3. FOOD DETAILS MODAL (Interactive Alpine Modal) -->
        <div x-show="selectedProduct !== null" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
            @keydown.escape.window="closeProduct()" style="display:none;">
            <div @click.away="closeProduct()"
                class="bg-white rounded-3xl overflow-hidden shadow-2xl max-w-lg w-full border border-slate-200">
                <template x-if="selectedProduct">
                    <div>
                        <div class="relative h-60 w-full overflow-hidden bg-slate-100">
                            <img :src="selectedProduct.image" :alt="selectedProduct.name"
                                class="w-full h-full object-cover">
                            <button @click="closeProduct()"
                                class="absolute top-3 right-3 h-9 w-9 bg-slate-900/70 hover:bg-slate-900 text-white rounded-full flex items-center justify-center transition">
                                <i class="ri-close-line text-lg"></i>
                            </button>
                            <span x-show="selectedProduct.is_buffet"
                                class="absolute top-3 left-3 bg-emerald-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow">BUFFET</span>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-semibold text-orange-600 bg-orange-50 px-2.5 py-1 rounded-full"
                                    x-text="selectedProduct.category"></span>
                                <span
                                    :class="selectedProduct.is_available ? 'bg-emerald-50 text-emerald-700' :
                                        'bg-red-50 text-red-700'"
                                    class="text-xs font-semibold px-2.5 py-1 rounded-full"
                                    x-text="selectedProduct.is_available ? '{{ __('storefront.available') }}' : '{{ __('storefront.sold_out') }}'"></span>
                            </div>
                            <h3 class="text-2xl font-bold text-slate-900 mt-3" x-text="selectedProduct.name"></h3>
                            <p x-show="selectedProduct.name_bn" class="text-sm text-slate-500 mt-0.5 font-medium"
                                x-text="selectedProduct.name_bn"></p>

                            <div class="flex items-center gap-2 mt-3">
                                <span class="text-xs text-slate-500 font-medium">Serving slots:</span>
                                <template x-for="slot in selectedProduct.meal_slots" :key="slot">
                                    <span
                                        class="text-[11px] font-semibold text-amber-800 bg-amber-50 rounded-full px-2.5 py-0.5"
                                        x-text="slot"></span>
                                </template>
                            </div>

                            <div
                                class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-sm text-slate-600 leading-relaxed">
                                <p class="font-medium text-slate-800 flex items-center gap-1.5 mb-1"><i
                                        class="ri-restaurant-line text-orange-600"></i> Chef's Selection</p>
                                Freshly prepared using quality ingredients. Customize with our table servers or scan table
                                QR code to order during your visit.
                            </div>

                            <div class="flex items-center justify-between mt-6 pt-4 border-t border-slate-100">
                                <div>
                                    <p class="text-xs text-slate-400 font-medium">Price</p>
                                    <p class="text-2xl font-extrabold text-orange-600" x-text="selectedProduct.price"></p>
                                </div>
                                <div class="flex gap-2">
                                    <a @click="closeProduct(); prefillReservation(selectedProduct.name)"
                                        href="#reservation"
                                        class="bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold px-5 py-2.5 rounded-full transition shadow-md shadow-orange-600/20 flex items-center gap-1.5">
                                        <i class="ri-calendar-check-line"></i>{{ __('storefront.reserve_table') }}
                                    </a>
                                    <button @click="closeProduct()"
                                        class="border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold px-4 py-2.5 rounded-full transition">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- 4. OFFERS & SPECIAL PROMOTIONS SECTION -->
        <section id="offers"
            class="bg-gradient-to-br from-amber-50 via-orange-50/40 to-slate-50 py-14 border-y border-orange-100">
            <div class="max-w-7xl mx-auto px-4">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <span
                        class="text-xs font-bold uppercase tracking-widest text-orange-600 bg-orange-100 rounded-full px-3 py-1">Limited
                        Time</span>
                    <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900 mt-3">
                        {{ __('storefront.special_offers') }}</h2>
                    <p class="text-slate-600 text-sm mt-2">Enjoy delicious savings on dine-in meals, family combos, and
                        special events.</p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <!-- Offer 1 -->
                    <div
                        class="bg-white border border-orange-200/80 rounded-3xl p-6 shadow-sm hover:shadow-md transition relative flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <span
                                class="text-xs font-extrabold text-white bg-orange-600 px-3 py-1 rounded-full uppercase tracking-wider">20%
                                OFF</span>
                            <i class="ri-fire-fill text-orange-500 text-xl"></i>
                        </div>
                        <h3 class="font-bold text-lg text-slate-900">Family Combo Feast</h3>
                        <p class="text-slate-500 text-xs mt-1.5 leading-relaxed flex-1">Order any 4 main courses and get a
                            flat 20% discount on your total table bill.</p>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 font-semibold">CODE</span>
                                <p class="text-xs font-mono font-bold text-slate-800">FEAST20</p>
                            </div>
                            <a href="#reservation"
                                class="text-xs font-bold text-orange-600 hover:text-orange-700 bg-orange-50 px-3 py-1.5 rounded-full transition">Claim
                                Offer</a>
                        </div>
                    </div>

                    <!-- Offer 2 -->
                    <div
                        class="bg-white border border-amber-200/80 rounded-3xl p-6 shadow-sm hover:shadow-md transition relative flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <span
                                class="text-xs font-extrabold text-white bg-amber-500 px-3 py-1 rounded-full uppercase tracking-wider">BOGO</span>
                            <i class="ri-cup-line text-amber-500 text-xl"></i>
                        </div>
                        <h3 class="font-bold text-lg text-slate-900">Happy Hour Drinks</h3>
                        <p class="text-slate-500 text-xs mt-1.5 leading-relaxed flex-1">Buy 1 Get 1 free on all refreshing
                            mocktails, smoothies and artisan shakes every weekday 4 PM - 7 PM.</p>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 font-semibold">HOURS</span>
                                <p class="text-xs font-mono font-bold text-slate-800">4PM-7PM</p>
                            </div>
                            <a href="#menu"
                                class="text-xs font-bold text-amber-700 hover:text-amber-800 bg-amber-50 px-3 py-1.5 rounded-full transition">View
                                Drinks</a>
                        </div>
                    </div>

                    <!-- Offer 3 -->
                    <div
                        class="bg-white border border-emerald-200/80 rounded-3xl p-6 shadow-sm hover:shadow-md transition relative flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <span
                                class="text-xs font-extrabold text-white bg-emerald-600 px-3 py-1 rounded-full uppercase tracking-wider">FREE
                                BONUS</span>
                            <i class="ri-cake-3-line text-emerald-500 text-xl"></i>
                        </div>
                        <h3 class="font-bold text-lg text-slate-900">Weekend Dessert Delight</h3>
                        <p class="text-slate-500 text-xs mt-1.5 leading-relaxed flex-1">Book a reservation for 4 or more
                            guests on Friday/Saturday and receive complimentary Chef's dessert.</p>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 font-semibold">TABLES</span>
                                <p class="text-xs font-mono font-bold text-slate-800">4+ Guests</p>
                            </div>
                            <a href="#reservation"
                                class="text-xs font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-full transition">Book
                                Table</a>
                        </div>
                    </div>

                    <!-- Offer 4 -->
                    <div
                        class="bg-white border border-purple-200/80 rounded-3xl p-6 shadow-sm hover:shadow-md transition relative flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <span
                                class="text-xs font-extrabold text-white bg-purple-600 px-3 py-1 rounded-full uppercase tracking-wider">SPECIAL</span>
                            <i class="ri-restaurant-2-line text-purple-500 text-xl"></i>
                        </div>
                        <h3 class="font-bold text-lg text-slate-900">Unlimited Buffet Deal</h3>
                        <p class="text-slate-500 text-xs mt-1.5 leading-relaxed flex-1">Explore 30+ items including
                            appetisers, live grill, chef curries and salads at all-inclusive group prices.</p>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 font-semibold">ALL DAY</span>
                                <p class="text-xs font-mono font-bold text-slate-800">Dine-in</p>
                            </div>
                            <a href="#reservation"
                                class="text-xs font-bold text-purple-700 hover:text-purple-800 bg-purple-50 px-3 py-1.5 rounded-full transition">Reserve
                                Buffet</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. ORDER ONLINE SECTION -->
        <section id="order-online" class="max-w-7xl mx-auto px-4 py-14">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span
                    class="text-xs font-bold uppercase tracking-widest text-orange-600 bg-orange-100 rounded-full px-3 py-1">Fast
                    & Convenient</span>
                <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900 mt-3">{{ __('storefront.how_to_order') }}
                </h2>
                <p class="text-slate-600 text-sm mt-2">Whether dining in, picking up takeaway, or ordering delivery — we
                    make it effortless.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <!-- Card 1: QR Dine-in -->
                <div
                    class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-md transition flex flex-col">
                    <div
                        class="h-12 w-12 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-2xl font-bold mb-4">
                        <i class="ri-qr-code-line"></i>
                    </div>
                    <h3 class="font-bold text-lg text-slate-900">1. Dine-In Table QR</h3>
                    <p class="text-slate-600 text-sm mt-2 leading-relaxed flex-1">
                        Sitting at one of our tables? Simply point your camera at the QR card on your table to view our
                        digital menu and send orders straight to the kitchen.
                    </p>
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        @if ($tables->isNotEmpty())
                            <a href="{{ route('menu.index', $tables->first()->id) }}" target="_blank"
                                class="w-full inline-flex items-center justify-center gap-1.5 bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2.5 px-4 rounded-xl text-xs transition">
                                <i class="ri-external-link-line"></i> Try Table Menu Demo ({{ $tables->first()->name }})
                            </a>
                        @else
                            <a href="#menu"
                                class="w-full inline-flex items-center justify-center bg-slate-100 text-slate-700 font-semibold py-2.5 px-4 rounded-xl text-xs">Browse
                                Menu Items</a>
                        @endif
                    </div>
                </div>

                <!-- Card 2: Takeaway / Pickup -->
                <div
                    class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-md transition flex flex-col">
                    <div
                        class="h-12 w-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl font-bold mb-4">
                        <i class="ri-takeaway-line"></i>
                    </div>
                    <h3 class="font-bold text-lg text-slate-900">2. Takeaway & Pickup</h3>
                    <p class="text-slate-600 text-sm mt-2 leading-relaxed flex-1">
                        Browse our menu, choose your dishes, and phone your preferred branch directly. Your order will be
                        packed hot and ready for quick pickup.
                    </p>
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        @if ($business?->phone)
                            <a href="tel:{{ $business->phone }}"
                                class="w-full inline-flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl text-xs transition">
                                <i class="ri-phone-fill"></i> Call to Order: {{ $business->phone }}
                            </a>
                        @else
                            <a href="#branches"
                                class="w-full inline-flex items-center justify-center bg-slate-100 text-slate-700 font-semibold py-2.5 px-4 rounded-xl text-xs">Select
                                Branch to Call</a>
                        @endif
                    </div>
                </div>

                <!-- Card 3: Home Delivery Desk -->
                <div
                    class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-md transition flex flex-col">
                    <div
                        class="h-12 w-12 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center text-2xl font-bold mb-4">
                        <i class="ri-riding-line"></i>
                    </div>
                    <h3 class="font-bold text-lg text-slate-900">3. Doorstep Delivery</h3>
                    <p class="text-slate-600 text-sm mt-2 leading-relaxed flex-1">
                        Hot meals delivered right to your home or office. Average delivery time is 30–45 minutes with
                        eco-friendly insulated packaging.
                    </p>
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <a href="#contact"
                            class="w-full inline-flex items-center justify-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2.5 px-4 rounded-xl text-xs transition">
                            <i class="ri-message-3-line"></i> Order via Delivery Desk
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Popular strip -->
        @if (($store['show_popular'] ?? '1') === '1' && $popular->isNotEmpty())
            <section id="popular" class="bg-white border-y border-slate-200">
                <div class="max-w-7xl mx-auto px-4 py-10">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-slate-900">{{ __('storefront.most_loved') }}</h2>
                        <span class="text-xs font-semibold text-orange-600">Top customer choices</span>
                    </div>
                    <div class="flex gap-4 overflow-x-auto no-scrollbar pb-2">
                        @foreach ($popular as $item)
                            <div @click="openProduct(@js([
    'id' => $item->id,
    'name' => $item->displayName(),
    'name_bn' => $item->name_bn,
    'price' => money($item->selling_price),
    'image' => $item->imageUrl(),
    'category' => $item->category?->name ?? 'Dish',
    'is_available' => $item->stock_in - $item->stock_out > 0,
    'is_buffet' => ($item->type instanceof \App\Enums\ProductType ? $item->type : \App\Enums\ProductType::tryFrom((string) $item->type)) === \App\Enums\ProductType::BUFFET,
    'meal_slots' => $item->meal_times?->map(fn($m) => $m instanceof \App\Enums\MealSlot ? $m->label() : ucfirst((string) $m))->all() ?? ['All day'],
]))"
                                class="shrink-0 w-48 bg-slate-50 hover:bg-white border border-slate-200 hover:border-orange-300 rounded-2xl p-3 cursor-pointer transition shadow-sm group">
                                <img src="{{ $item->imageUrl() }}" alt="{{ $item->displayName() }}"
                                    class="h-28 w-full object-cover rounded-xl group-hover:scale-105 transition duration-300"
                                    loading="lazy">
                                <p class="font-semibold text-sm truncate mt-2 group-hover:text-orange-600 transition">
                                    {{ $item->displayName() }}</p>
                                <p class="text-orange-600 font-extrabold text-sm mt-0.5">{{ money($item->selling_price) }}
                                </p>
                                <p class="text-[11px] text-slate-400 mt-1 flex items-center justify-between">
                                    <span>{{ $item->stock_out }} {{ __('storefront.sold') }}</span>
                                    <span class="text-orange-500 font-semibold"><i class="ri-eye-line"></i> View</span>
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- 6. RESERVE TABLE SECTION -->
        @if (($store['show_reservation'] ?? '1') === '1')
            <section id="reservation" class="max-w-7xl mx-auto px-4 py-14">
                <div class="grid lg:grid-cols-5 gap-8 items-start">
                    <div class="lg:col-span-2">
                        <span
                            class="text-xs font-bold uppercase tracking-widest text-orange-600 bg-orange-100 rounded-full px-3 py-1">Instant
                            Booking</span>
                        <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900 mt-3">
                            {{ __('storefront.reserve_title') }}</h2>
                        <p class="text-sm text-slate-500 mt-2">{{ __('storefront.reserve_note') }}</p>
                        <ul class="mt-6 space-y-3 text-sm text-slate-600">
                            <li class="flex items-center gap-2.5"><i
                                    class="ri-checkbox-circle-fill text-emerald-500 text-lg"></i>{{ __('storefront.reserve_free') }}
                            </li>
                            <li class="flex items-center gap-2.5"><i
                                    class="ri-checkbox-circle-fill text-emerald-500 text-lg"></i>{{ __('storefront.reserve_confirm') }}
                            </li>
                            <li class="flex items-center gap-2.5"><i
                                    class="ri-checkbox-circle-fill text-emerald-500 text-lg"></i>{{ __('storefront.reserve_qr') }}
                            </li>
                        </ul>
                        @if ($business?->phone)
                            <div class="mt-6 p-4 rounded-2xl bg-orange-50/60 border border-orange-200/70">
                                <p class="text-xs text-orange-800 font-medium">Need immediate table assistance?</p>
                                <a href="tel:{{ $business->phone }}"
                                    class="inline-flex items-center gap-2 font-bold text-orange-700 text-base mt-1"><i
                                        class="ri-phone-line"></i>{{ $business->phone }}</a>
                            </div>
                        @endif
                    </div>
                    <form id="reserve-form"
                        class="lg:col-span-3 bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm grid sm:grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <label class="text-xs font-semibold text-slate-700">{{ __('storefront.your_name') }}
                                *</label>
                            <input name="customer_name" required
                                class="mt-1.5 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="{{ __('storefront.full_name') }}">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700">{{ __('storefront.phone') }} *</label>
                            <input name="customer_phone" required
                                class="mt-1.5 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="01XXXXXXXXX">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700">{{ __('storefront.guests') }} *</label>
                            <input name="guest_count" type="number" min="1" max="100" value="2"
                                required
                                class="mt-1.5 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700">{{ __('storefront.datetime') }} *</label>
                            <input name="reservation_time" type="datetime-local" required
                                class="mt-1.5 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        @if ($branches->isNotEmpty())
                            <div>
                                <label class="text-xs font-semibold text-slate-700">{{ __('storefront.branch') }}</label>
                                <select id="branch-select" name="branch_id"
                                    class="mt-1.5 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <option value="">{{ __('storefront.any_branch') }}</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label class="text-xs font-semibold text-slate-700">{{ __('storefront.table') }} *</label>
                            <select id="table-select" name="table_id" required
                                class="mt-1.5 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                                @forelse ($tables as $table)
                                    <option value="{{ $table->id }}" data-branch="{{ $table->branch_id }}">
                                        {{ $table->name }}</option>
                                @empty
                                    <option value="" disabled>{{ __('storefront.no_free_tables') }}</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label
                                class="text-xs font-semibold text-slate-700">{{ __('storefront.notes_optional') }}</label>
                            <textarea id="reserve-notes" name="notes" rows="2"
                                class="mt-1.5 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="Birthday celebration, window seat, special dishes..."></textarea>
                        </div>
                        <div class="sm:col-span-2 mt-2">
                            <button type="submit" id="reserve-btn"
                                class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 rounded-xl text-sm transition shadow-lg shadow-orange-600/20 disabled:opacity-60 flex items-center justify-center gap-2">
                                <i class="ri-calendar-check-line"></i> {{ __('storefront.request_reservation') }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        @endif

        <!-- 7. PHOTO GALLERY SECTION -->
        <section id="gallery" class="bg-white border-y border-slate-200 py-14" x-data="{ galleryFilter: 'all', lightboxSrc: null }">
            <div class="max-w-7xl mx-auto px-4">
                <div class="text-center max-w-2xl mx-auto mb-8">
                    <span
                        class="text-xs font-bold uppercase tracking-widest text-orange-600 bg-orange-100 rounded-full px-3 py-1">Memories
                        & Ambiance</span>
                    <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900 mt-3">
                        {{ __('storefront.restaurant_gallery') }}</h2>
                    <p class="text-slate-600 text-sm mt-2">Take a visual tour through our dining spaces, master creations,
                        and customer moments.</p>
                </div>

                <!-- Filter Tags -->
                <div class="flex justify-center gap-2 mb-8 overflow-x-auto no-scrollbar">
                    <button @click="galleryFilter = 'all'"
                        :class="galleryFilter === 'all' ? 'bg-slate-900 text-white shadow-sm' :
                            'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="text-xs sm:text-sm font-semibold px-4 py-2 rounded-full transition">All Photos</button>
                    <button @click="galleryFilter = 'dishes'"
                        :class="galleryFilter === 'dishes' ? 'bg-slate-900 text-white shadow-sm' :
                            'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="text-xs sm:text-sm font-semibold px-4 py-2 rounded-full transition">Signature
                        Dishes</button>
                    <button @click="galleryFilter = 'ambience'"
                        :class="galleryFilter === 'ambience' ? 'bg-slate-900 text-white shadow-sm' :
                            'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="text-xs sm:text-sm font-semibold px-4 py-2 rounded-full transition">Ambience &
                        Tables</button>
                    <button @click="galleryFilter = 'drinks'"
                        :class="galleryFilter === 'drinks' ? 'bg-slate-900 text-white shadow-sm' :
                            'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="text-xs sm:text-sm font-semibold px-4 py-2 rounded-full transition">Beverages</button>
                </div>

                <!-- Gallery Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @php
                        $galleryItems = [
                            [
                                'cat' => 'dishes',
                                'title' => 'Woodfired Gourmet Pizza',
                                'img' =>
                                    'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=800&q=80',
                            ],
                            [
                                'cat' => 'ambience',
                                'title' => 'Cozy Dining Hall',
                                'img' =>
                                    'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80',
                            ],
                            [
                                'cat' => 'dishes',
                                'title' => 'Signature Beef Steak',
                                'img' =>
                                    'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80',
                            ],
                            [
                                'cat' => 'drinks',
                                'title' => 'Artisan Mojito & Cocktails',
                                'img' =>
                                    'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=800&q=80',
                            ],
                            [
                                'cat' => 'ambience',
                                'title' => 'Romantic Window Tables',
                                'img' =>
                                    'https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?auto=format&fit=crop&w=800&q=80',
                            ],
                            [
                                'cat' => 'dishes',
                                'title' => 'Creamy Tagliatelle Pasta',
                                'img' =>
                                    'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=800&q=80',
                            ],
                            [
                                'cat' => 'ambience',
                                'title' => 'Private Banquet Area',
                                'img' =>
                                    'https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=800&q=80',
                            ],
                            [
                                'cat' => 'drinks',
                                'title' => 'Cold Brew & Smoothies',
                                'img' =>
                                    'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=800&q=80',
                            ],
                        ];
                    @endphp
                    @foreach ($galleryItems as $item)
                        <div x-show="galleryFilter === 'all' || galleryFilter === '{{ $item['cat'] }}'"
                            @click="lightboxSrc = '{{ $item['img'] }}'"
                            class="group relative h-48 sm:h-56 rounded-2xl overflow-hidden cursor-pointer shadow-sm hover:shadow-lg transition duration-300">
                            <img src="{{ $item['img'] }}" alt="{{ $item['title'] }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                                loading="lazy">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent opacity-0 group-hover:opacity-100 transition duration-300 flex flex-col justify-end p-3.5">
                                <span class="text-white font-bold text-sm">{{ $item['title'] }}</span>
                                <span class="text-orange-400 text-xs flex items-center gap-1 mt-0.5"><i
                                        class="ri-zoom-in-line"></i> Click to enlarge</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Lightbox Modal -->
                <div x-show="lightboxSrc !== null" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
                    @keydown.escape.window="lightboxSrc = null" style="display:none;">
                    <div @click.away="lightboxSrc = null" class="relative max-w-3xl w-full">
                        <button @click="lightboxSrc = null"
                            class="absolute -top-12 right-0 text-white hover:text-orange-400 text-3xl font-bold transition">
                            <i class="ri-close-line"></i>
                        </button>
                        <img :src="lightboxSrc" class="w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl">
                    </div>
                </div>
            </div>
        </section>

        <!-- 8. BRANCHES SECTION -->
        @if (($store['show_branches'] ?? '1') === '1' && $branches->isNotEmpty())
            <section id="branches" class="max-w-7xl mx-auto px-4 py-14">
                <div class="flex flex-wrap items-end justify-between gap-3 mb-8">
                    <div>
                        <span
                            class="text-xs font-bold uppercase tracking-widest text-orange-600 bg-orange-100 rounded-full px-3 py-1">{{ __('storefront.find_us') }}</span>
                        <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900 mt-2">
                            {{ __('storefront.branches') }}</h2>
                        <p class="text-slate-500 text-sm mt-1">Visit your nearest branch for prime dining or quick pickup.
                        </p>
                    </div>
                    <a href="#reservation"
                        class="text-sm font-semibold text-orange-600 hover:text-orange-700 flex items-center gap-1">
                        {{ __('storefront.reserve_table') }} <i class="ri-arrow-right-line"></i>
                    </a>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($branches as $branch)
                        <div
                            class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-md transition flex flex-col">
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-lg text-slate-900 flex items-center gap-2">
                                    <span
                                        class="h-8 w-8 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-base"><i
                                            class="ri-store-2-line"></i></span>
                                    {{ $branch->name }}
                                </p>
                                @if ($branch->is_default)
                                    <span
                                        class="text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-full px-2.5 py-0.5">{{ __('storefront.head_branch') }}</span>
                                @endif
                            </div>
                            @if ($branch->address)
                                <p class="text-sm text-slate-500 mt-3 flex items-start gap-2 flex-1">
                                    <i class="ri-map-pin-line text-slate-400 mt-0.5 shrink-0"></i>{{ $branch->address }}
                                </p>
                            @endif
                            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                                @if ($branch->phone)
                                    <a href="tel:{{ $branch->phone }}"
                                        class="text-xs font-bold text-orange-700 bg-orange-50 hover:bg-orange-100 px-3.5 py-2 rounded-xl transition flex items-center gap-1.5">
                                        <i class="ri-phone-fill"></i>{{ $branch->phone }}
                                    </a>
                                @endif
                                <a href="#reservation"
                                    class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
                                    Book Here <i class="ri-calendar-line"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- 9. CONTACT SECTION -->
        <section id="contact" class="bg-slate-100/70 border-t border-slate-200 py-14">
            <div class="max-w-7xl mx-auto px-4">
                <div class="grid lg:grid-cols-5 gap-8 items-start">
                    <!-- Left Info Column -->
                    <div class="lg:col-span-2">
                        <span
                            class="text-xs font-bold uppercase tracking-widest text-orange-600 bg-orange-100 rounded-full px-3 py-1">{{ __('storefront.get_in_touch') }}</span>
                        <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900 mt-3">
                            {{ __('storefront.contact_us') }}</h2>
                        <p class="text-slate-600 text-sm mt-2 leading-relaxed">
                            Have questions about private catering, banquet bookings, table reservations or general feedback?
                            Send us a note or call directly.
                        </p>

                        <div class="space-y-3 mt-6">
                            @if ($business?->phone)
                                <a href="tel:{{ $business->phone }}"
                                    class="flex items-center gap-3 bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-orange-300 transition group shadow-sm">
                                    <span
                                        class="h-10 w-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-orange-600 group-hover:text-white transition"><i
                                            class="ri-phone-fill"></i></span>
                                    <div>
                                        <p class="text-[11px] font-semibold text-slate-400 uppercase">Call Directly</p>
                                        <p class="text-sm font-bold text-slate-900">{{ $business->phone }}</p>
                                    </div>
                                </a>
                            @endif

                            @if ($business?->email)
                                <a href="mailto:{{ $business->email }}"
                                    class="flex items-center gap-3 bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-orange-300 transition group shadow-sm">
                                    <span
                                        class="h-10 w-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-blue-600 group-hover:text-white transition"><i
                                            class="ri-mail-fill"></i></span>
                                    <div>
                                        <p class="text-[11px] font-semibold text-slate-400 uppercase">Email Support</p>
                                        <p class="text-sm font-bold text-slate-900">{{ $business->email }}</p>
                                    </div>
                                </a>
                            @endif

                            @if (!empty($store['opening_hours']))
                                <div
                                    class="flex items-center gap-3 bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm">
                                    <span
                                        class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg shrink-0"><i
                                            class="ri-time-fill"></i></span>
                                    <div>
                                        <p class="text-[11px] font-semibold text-slate-400 uppercase">Operating Hours</p>
                                        <p class="text-sm font-bold text-slate-900">{{ $store['opening_hours'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right Form Column -->
                    <form id="contact-form"
                        class="lg:col-span-3 bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm grid sm:grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <label class="text-xs font-semibold text-slate-700">{{ __('storefront.full_name') }}
                                *</label>
                            <input name="name" required
                                class="mt-1.5 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="John Doe">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700">{{ __('storefront.phone') }} *</label>
                            <input name="phone" required
                                class="mt-1.5 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="01XXXXXXXXX">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700">{{ __('storefront.email') }}
                                (optional)</label>
                            <input name="email" type="email"
                                class="mt-1.5 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="your@email.com">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700">{{ __('storefront.subject') }}
                                (optional)</label>
                            <input name="subject"
                                class="mt-1.5 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="Catering, feedback, etc.">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-700">{{ __('storefront.message') }} *</label>
                            <textarea name="message" rows="3" required
                                class="mt-1.5 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="Write your message here..."></textarea>
                        </div>
                        <div class="sm:col-span-2 mt-2">
                            <button type="submit" id="contact-btn"
                                class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3 rounded-xl text-sm transition shadow-md disabled:opacity-60 flex items-center justify-center gap-2">
                                <i class="ri-send-plane-fill"></i> {{ __('storefront.send_message') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 text-slate-400">
            <div class="max-w-7xl mx-auto px-4 py-12">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 pb-10 border-b border-slate-800 text-sm">
                    <!-- Brand -->
                    <div class="sm:col-span-2 lg:col-span-1">
                        <a href="#top" class="flex items-center gap-2 font-bold text-white text-lg">
                            @if (store_logo_url())
                                <img src="{{ store_logo_url() }}" alt="{{ store_name() }}"
                                    class="h-8 w-8 rounded-lg object-cover">
                            @else
                                <span
                                    class="flex items-center justify-center h-8 w-8 rounded-lg bg-orange-600 text-white text-base"><i
                                        class="ri-restaurant-2-line"></i></span>
                            @endif
                            <span>{{ $business->name ?? store_name() }}</span>
                        </a>
                        <p class="text-xs text-slate-400 mt-3 leading-relaxed">
                            {{ $store['hero_subtitle'] ?: __('storefront.hero_tagline') }}</p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <p class="text-white font-bold mb-3 uppercase tracking-wider text-xs">Explore</p>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#top" class="hover:text-white transition">{{ __('storefront.home') }}</a>
                            </li>
                            <li><a href="#menu" class="hover:text-white transition">{{ __('storefront.menu') }}</a>
                            </li>
                            <li><a href="#offers" class="hover:text-white transition">{{ __('storefront.offers') }}</a>
                            </li>
                            <li><a href="#order-online"
                                    class="hover:text-white transition">{{ __('storefront.order_online') }}</a></li>
                        </ul>
                    </div>

                    <!-- Dining -->
                    <div>
                        <p class="text-white font-bold mb-3 uppercase tracking-wider text-xs">Services</p>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#reservation"
                                    class="hover:text-white transition">{{ __('storefront.reserve_table') }}</a></li>
                            <li><a href="#gallery"
                                    class="hover:text-white transition">{{ __('storefront.gallery') }}</a></li>
                            <li><a href="#branches"
                                    class="hover:text-white transition">{{ __('storefront.branches') }}</a></li>
                            <li><a href="#contact"
                                    class="hover:text-white transition">{{ __('storefront.contact') }}</a></li>
                        </ul>
                    </div>

                    <!-- Staff -->
                    <div>
                        <p class="text-white font-bold mb-3 uppercase tracking-wider text-xs">Portal</p>
                        <ul class="space-y-2 text-xs">
                            <li><a href="{{ route('login') }}"
                                    class="inline-flex items-center gap-1.5 text-orange-400 hover:text-orange-300 font-semibold"><i
                                        class="ri-lock-line"></i> {{ __('storefront.staff_login') }}</a></li>
                            @if ($business?->phone)
                                <li><a href="tel:{{ $business->phone }}" class="hover:text-white transition"><i
                                            class="ri-phone-line mr-1"></i>{{ $business->phone }}</a></li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="pt-6 flex flex-col md:flex-row items-center justify-between gap-3 text-xs">
                    <p>© {{ date('Y') }} {{ $business->name ?? config('app.name') }}.
                        {{ $store['footer_note'] ?: __('storefront.rights') }}</p>
                    <p class="text-slate-500">Fine dining & automated restaurant management.</p>
                </div>
            </div>
        </footer>

        <script>
            function storefront() {
                return {
                    query: '',
                    category: '',
                    slot: @js($currentSlot ?? 'all'),
                    mobileMenu: false,
                    selectedProduct: null,
                    openProduct(product) {
                        this.selectedProduct = product;
                    },
                    closeProduct() {
                        this.selectedProduct = null;
                    },
                    prefillReservation(dishName) {
                        const notes = document.getElementById('reserve-notes');
                        if (notes) {
                            notes.value = 'Interested in dish: ' + dishName + (notes.value ? ('\n' + notes.value) : '');
                        }
                    }
                };
            }

            (function() {
                // Branch and Table selector synchronization
                const branchSel = document.getElementById('branch-select');
                const tableSel = document.getElementById('table-select');
                if (branchSel && tableSel) {
                    const all = Array.from(tableSel.options).map(o => ({
                        value: o.value,
                        text: o.text,
                        branch: o.dataset.branch
                    }));
                    branchSel.addEventListener('change', () => {
                        const b = branchSel.value;
                        tableSel.innerHTML = '';
                        all.filter(o => !b || o.branch === b).forEach(o => {
                            const opt = document.createElement('option');
                            opt.value = o.value;
                            opt.textContent = o.text;
                            tableSel.appendChild(opt);
                        });
                        if (!tableSel.options.length) {
                            const opt = document.createElement('option');
                            opt.disabled = true;
                            opt.textContent = '{{ __('storefront.no_free_tables_branch') }}';
                            tableSel.appendChild(opt);
                        }
                    });
                }

                // Reservation Form AJAX submission
                const reserveForm = document.getElementById('reserve-form');
                reserveForm?.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const btn = document.getElementById('reserve-btn');
                    btn.disabled = true;
                    try {
                        const res = await fetch('{{ route('storefront.reserve') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Accept': 'application/json',
                            },
                            body: new FormData(reserveForm),
                        });
                        const data = await res.json();
                        if (!res.ok || !data.status) {
                            const msg = data.message || Object.values(data.errors || {}).flat().join(' ') ||
                                'Reservation failed.';
                            throw new Error(msg);
                        }
                        window.toast?.success(data.message);
                        reserveForm.reset();
                    } catch (err) {
                        window.toast?.error(err.message || 'Reservation failed.');
                    } finally {
                        btn.disabled = false;
                    }
                });

                // Contact Form AJAX submission
                const contactForm = document.getElementById('contact-form');
                contactForm?.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const btn = document.getElementById('contact-btn');
                    btn.disabled = true;
                    try {
                        const res = await fetch('{{ route('storefront.contact') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Accept': 'application/json',
                            },
                            body: new FormData(contactForm),
                        });
                        const data = await res.json();
                        if (!res.ok || !data.status) {
                            const msg = data.message || Object.values(data.errors || {}).flat().join(' ') ||
                                'Failed to send message.';
                            throw new Error(msg);
                        }
                        window.toast?.success(data.message);
                        contactForm.reset();
                    } catch (err) {
                        window.toast?.error(err.message || 'Failed to send message.');
                    } finally {
                        btn.disabled = false;
                    }
                });
            })();
        </script>
    </body>

    </html>
