<nav class="bg-white border-b border-slate-200 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('admin.pos.index') }}" class="flex items-center gap-2 text-slate-900 font-semibold">
                <span class="flex items-center justify-center h-8 w-8 rounded-lg bg-brand-600 text-white">
                    <i class="ri-restaurant-2-line"></i>
                </span>
                <span>POS</span>
            </a>
            <ul class="hidden md:flex items-center gap-1">
                <li><a class="nav-link" href="{{ route('admin.products.index') }}">Products</a></li>
                <li><a class="nav-link" href="{{ route('admin.customers.index') }}">Customers</a></li>
                <li><a class="nav-link" href="{{ route('admin.sales.index') }}">Sales</a></li>
                <li><a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            </ul>
            <a class="btn btn-ghost btn-sm" href="{{ route('logout') }}">
                <i class="ri-logout-box-r-line"></i> Logout
            </a>
        </div>
    </div>
</nav>

