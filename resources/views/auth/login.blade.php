@extends('layouts.auth')
@section('title', 'POS Terminal Sign in')
@section('content')

<div class="min-h-screen grid grid-cols-1 lg:grid-cols-12 bg-slate-950 text-slate-100 overflow-hidden">

    {{-- ==================== LEFT: HERO SHOWCASE (IMAGE & BRANDING) ==================== --}}
    <div class="relative hidden lg:flex lg:col-span-7 xl:col-span-7 flex-col justify-between p-10 xl:p-14 select-none overflow-hidden">
        {{-- High quality ambient restaurant hero image --}}
        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-1000 ease-out hover:scale-105"
             style="background-image: url('{{ asset('assets/images/pos-login-hero.jpg') }}');">
        </div>

        {{-- Deep cinematic gradient overlay --}}
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-slate-950/30"></div>
        <div class="absolute inset-0 bg-radial from-brand-500/10 via-transparent to-slate-950/60"></div>

        {{-- Top Brand Bar --}}
        <div class="relative z-10 flex items-center justify-between">
            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-2xl bg-slate-950/70 backdrop-blur-md border border-white/10 shadow-2xl">
                @if (store_logo_url())
                    <img src="{{ store_logo_url() }}" alt="{{ store_name() }}" class="h-8 w-8 rounded-xl object-cover">
                @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-brand-500 text-white shadow-md">
                        <i class="ri-restaurant-2-fill text-lg"></i>
                    </span>
                @endif
                <div class="flex items-center gap-2">
                    <span class="font-bold text-sm tracking-tight text-white">{{ store_name() }}</span>
                    <span class="h-1 w-1 rounded-full bg-slate-500"></span>
                    <span class="text-xs text-brand-300 font-semibold">POS Terminal</span>
                </div>
            </div>

            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/15 backdrop-blur-md border border-emerald-500/30 text-emerald-300 text-xs font-semibold">
                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>System Online</span>
            </div>
        </div>

        {{-- Middle & Bottom Hero Message --}}
        <div class="relative z-10 max-w-xl my-auto py-12">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/20 border border-brand-400/30 text-brand-300 text-xs font-bold uppercase tracking-wider mb-5">
                <i class="ri-flashlight-fill text-brand-400"></i> Next-Gen Restaurant Management
            </div>

            <h1 class="text-3xl xl:text-4xl 2xl:text-5xl font-black text-white tracking-tight leading-[1.15] mb-4">
                Smart Dining, <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-300 via-brand-400 to-amber-200">
                    Effortless Operations.
                </span>
            </h1>

            <p class="text-sm xl:text-base text-slate-300/90 leading-relaxed mb-8">
                Empower your front-of-house team with rapid table orders, instant kitchen display communication, and real-time revenue analytics.
            </p>

            {{-- Feature highlights with glassmorphic cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-3.5 rounded-xl bg-slate-900/60 backdrop-blur-md border border-white/10 hover:border-brand-500/40 transition">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500/20 text-brand-400 mb-2">
                        <i class="ri-calculator-line text-base"></i>
                    </div>
                    <div class="font-bold text-xs text-white">Speed POS</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">1-click orders & fast bills</div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-900/60 backdrop-blur-md border border-white/10 hover:border-brand-500/40 transition">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/20 text-amber-400 mb-2">
                        <i class="ri-fire-line text-base"></i>
                    </div>
                    <div class="font-bold text-xs text-white">Live KDS</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Instant kitchen dispatch</div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-900/60 backdrop-blur-md border border-white/10 hover:border-brand-500/40 transition">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/20 text-sky-400 mb-2">
                        <i class="ri-line-chart-line text-base"></i>
                    </div>
                    <div class="font-bold text-xs text-white">Live Shift Analytics</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Real-time daily ledger</div>
                </div>
            </div>
        </div>

        {{-- Bottom Status Bar --}}
        <div class="relative z-10 flex items-center justify-between pt-6 border-t border-white/10 text-xs text-slate-400">
            <span class="flex items-center gap-2">
                <i class="ri-shield-check-fill text-brand-400 text-sm"></i>
                <span>Enterprise grade security &middot; 256-bit TLS</span>
            </span>
            <span>&copy; {{ date('Y') }} {{ store_name() }}</span>
        </div>
    </div>

    {{-- ==================== RIGHT: MODERN LOGIN STATION ==================== --}}
    <div class="lg:col-span-5 xl:col-span-5 flex flex-col justify-between p-6 sm:p-10 lg:p-12 xl:p-14 bg-slate-950 relative z-10 border-l border-slate-800/80 min-h-screen">

        {{-- Top Bar (Brand visibility for mobile / station indicator) --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                @if (store_logo_url())
                    <img src="{{ store_logo_url() }}" alt="{{ store_name() }}" class="h-10 w-10 rounded-xl object-cover bg-white/10">
                @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-md">
                        <i class="ri-restaurant-2-line text-xl"></i>
                    </span>
                @endif
                <div>
                    <h2 class="font-black text-base tracking-tight text-white leading-tight">{{ store_name() }}</h2>
                    <p class="text-xs text-slate-400">Restaurant Point of Sale</p>
                </div>
            </div>

            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-900 border border-slate-800 text-slate-400">
                <i class="ri-macbook-line"></i> Station 01
            </div>
        </div>

        {{-- Main Login Card Area --}}
        <div class="w-full max-w-md mx-auto my-auto py-4"
             x-data="{
                showPassword: false,
                email: '{{ old('email') }}',
                password: '',
                fill(e, p) {
                    this.email = e;
                    this.password = p;
                }
             }">

            <div class="mb-6">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-500/10 text-brand-400 border border-brand-500/20 mb-3">
                    <i class="ri-shield-keyhole-line"></i> Terminal Authentication
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Sign in to Terminal</h2>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-400">Enter your operator or staff credentials to proceed.</p>
            </div>

            <x-flash-message />

            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-red-500/10 border border-red-500/30 p-3.5 text-xs text-red-300 space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <i class="ri-error-warning-line text-sm text-red-400 shrink-0"></i>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                {{-- Email Input --}}
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="ri-mail-line text-base"></i>
                        </div>
                        <input type="email"
                               id="email"
                               name="email"
                               x-model="email"
                               class="w-full bg-slate-900/90 border border-slate-800 text-white rounded-xl py-3 pl-10 pr-4 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none placeholder-slate-500 transition shadow-inner"
                               placeholder="admin@gmail.com"
                               required
                               autofocus
                               autocomplete="username">
                    </div>
                </div>

                {{-- Password Input --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Password
                        </label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="ri-lock-2-line text-base"></i>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'"
                               id="password"
                               name="password"
                               x-model="password"
                               class="w-full bg-slate-900/90 border border-slate-800 text-white rounded-xl py-3 pl-10 pr-11 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none placeholder-slate-500 transition shadow-inner font-mono tracking-wider"
                               placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                               required
                               autocomplete="current-password">
                        <button type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition focus:outline-none"
                                tabindex="-1"
                                :title="showPassword ? 'Hide password' : 'Show password'">
                            <i :class="showPassword ? 'ri-eye-off-line' : 'ri-eye-line'" class="text-base"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-xs text-slate-400 hover:text-slate-300 transition">
                        <input type="checkbox"
                               name="remember"
                               id="remembercheck"
                               class="rounded border-slate-700 bg-slate-900 text-brand-600 focus:ring-brand-500/30">
                        <span>Remember this terminal</span>
                    </label>

                    <span class="text-[11px] text-slate-500 flex items-center gap-1">
                        <i class="ri-lock-line"></i> Station Encrypted
                    </span>
                </div>

                {{-- Submit Button --}}
                <button type="submit"
                        class="w-full mt-2 py-3.5 px-6 rounded-xl font-bold text-white bg-gradient-to-r from-brand-500 via-brand-600 to-brand-700 hover:from-brand-600 hover:to-brand-800 shadow-lg shadow-brand-600/30 hover:shadow-brand-600/50 active:scale-[0.99] transition-all flex items-center justify-center gap-2 group cursor-pointer text-sm">
                    <span>Sign in to Terminal</span>
                    <i class="ri-arrow-right-line group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>

            {{-- Quick Login Credentials for Testing / Staff Switch --}}
            <div class="mt-8 pt-6 border-t border-slate-800/80">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        Quick Demo Credentials
                    </span>
                    <span class="text-[10px] text-slate-500">Click to fill</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button"
                            @click="fill('admin@gmail.com', 'password')"
                            class="px-2.5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-brand-500/40 text-left transition group cursor-pointer">
                        <span class="block text-[11px] font-bold text-white group-hover:text-brand-400 transition">Admin</span>
                        <span class="block text-[9px] text-slate-500 truncate">admin@gmail.com</span>
                    </button>
                    <button type="button"
                            @click="fill('cashier@gmail.com', 'password')"
                            class="px-2.5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-brand-500/40 text-left transition group cursor-pointer">
                        <span class="block text-[11px] font-bold text-white group-hover:text-brand-400 transition">Cashier</span>
                        <span class="block text-[9px] text-slate-500 truncate">cashier@gmail.com</span>
                    </button>
                    <button type="button"
                            @click="fill('manager@gmail.com', 'password')"
                            class="px-2.5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-brand-500/40 text-left transition group cursor-pointer">
                        <span class="block text-[11px] font-bold text-white group-hover:text-brand-400 transition">Manager</span>
                        <span class="block text-[9px] text-slate-500 truncate">manager@gmail.com</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Bottom Copyright / Station ID --}}
        <div class="pt-6 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2">
            <span>&copy; {{ date('Y') }} {{ store_name() }} &middot; POS System</span>
            <span class="flex items-center gap-1.5 text-slate-500">
                <i class="ri-shield-check-line text-emerald-400"></i> Authorized Staff Access Only
            </span>
        </div>
    </div>

</div>

@endsection
