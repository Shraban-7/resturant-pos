<div class="flex items-center gap-3 min-w-0">
    <button type="button" class="lg:hidden btn btn-ghost btn-icon -ml-2" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle sidebar">
        <i class="ri-menu-line text-xl"></i>
    </button>

    <div class="min-w-0">
        <h1 class="text-base sm:text-lg font-semibold text-slate-900 truncate">@yield('page_title', 'Dashboard')</h1>
        <div class="text-xs text-slate-500 truncate">
            @hasSection('breadcrumb')
                @yield('breadcrumb')
            @else
                @yield('title')
            @endif
        </div>
    </div>
</div>

<div class="flex items-center gap-2">
    @if (is_seller() && seller_branches()->isNotEmpty())
        <form action="{{ route('seller.branches.switch') }}" method="post" class="hidden sm:block">
            @csrf
            <select name="branch_id" class="form-control form-control-sm min-w-[10rem]" onchange="this.form.submit()">
                <option value="" @selected(is_all_branches_mode())>All branches</option>
                @foreach (seller_branches() as $branch)
                    <option value="{{ $branch->id }}" @selected((int) active_branch_id() === (int) $branch->id && ! is_all_branches_mode())>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </form>
    @endif

    <div class="relative hidden sm:block" x-data="{ open: false }">
        <button type="button" class="btn btn-ghost btn-icon" aria-label="Notifications">
            <i class="ri-notification-3-line text-lg"></i>
            <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-red-500"></span>
        </button>
    </div>

    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button type="button" class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-slate-100 transition" @click="open = !open" :aria-expanded="open">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-white text-sm font-semibold">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </span>
            <span class="hidden md:flex flex-col items-start leading-tight">
                <span class="text-sm font-medium text-slate-900">{{ auth()->user()->name ?? 'User' }}</span>
                <span class="text-[10px] uppercase tracking-wider text-slate-400">{{ is_supplier() ? 'Supplier' : 'Seller' }}</span>
            </span>
            <i class="ri-arrow-down-s-line text-slate-400"></i>
        </button>
        <div x-show="open" @click.outside="open = false" x-transition class="dropdown-menu" style="display:none">
            <a href="{{ route(is_supplier() ? 'supplier.settings.index' : 'seller.settings.index') }}" class="dropdown-item">
                <i class="ri-settings-3-line me-2"></i> Settings
            </a>
            <div class="border-t border-slate-100 my-1"></div>
            <a href="{{ route('logout') }}" class="dropdown-item text-red-600">
                <i class="ri-logout-box-r-line me-2"></i> Logout
            </a>
        </div>
    </div>
</div>
