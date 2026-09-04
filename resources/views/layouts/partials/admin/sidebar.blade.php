<div class="flex flex-col h-full">
    <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
        <span class="flex items-center justify-center h-9 w-9 rounded-lg bg-brand-600 text-white">
            <i class="ri-restaurant-2-line text-lg"></i>
        </span>
        <div class="flex flex-col leading-tight">
            <span class="text-sm font-semibold text-slate-900">{{ config('app.name', env('APP_NAME')) }}</span>
            <span class="text-[10px] uppercase tracking-wider text-slate-400">{{ is_employee() ? 'Employee' : 'Admin' }}</span>
        </div>
    </a>

    <nav class="flex-1 overflow-y-auto pb-4">
        <div class="sidebar-section">Main</div>
        <ul class="sidebar-list">
            @can('dashboard')
                <x-sidebar-list-item :title="'Dashboard'" :icon="'ri-dashboard-line'" :route="'admin.dashboard'" />
            @endcan
            @can('pos')
                <x-sidebar-list-item :title="'POS'" :icon="'ri-shopping-cart-2-line'" :route="'admin.pos.index'" />
            @endcan
        </ul>

        @canany(['products', 'stocks'])
            <div class="sidebar-section">Inventory</div>
            <ul class="sidebar-list">
                @can('products')
                    <x-sidebar-list-item :title="'Products'" :icon="'ri-box-3-line'" :route="'admin.products.index'" />
                @endcan
                @can('stocks')
                    <x-sidebar-list-item :title="'Stock History'" :icon="'ri-stock-line'" :route="'admin.stocks.index'" />
                @endcan
            </ul>
        @endcanany

        @canany(['sales', 'kds', 'floors', 'branches', 'reservations'])
            <div class="sidebar-section">Operations</div>
            <ul class="sidebar-list">
                @can('sales')
                    <x-sidebar-list-item :title="'Sales'" :icon="'ri-file-paper-2-line'" :route="'admin.sales.index'" />
                @endcan
                @can('kds')
                    <x-sidebar-list-item :title="'Kitchen Display'" :icon="'ri-tablet-line'" :route="'admin.kds.index'" />
                @endcan
                @can('floors')
                    <x-sidebar-list-item :title="'Floors'" :icon="'ri-building-line'" :route="'admin.floors.index'" />
                    <x-sidebar-list-item :title="'Dining Tables'" :icon="'ri-reserved-line'" :route="'admin.diningTables.index'" />
                @endcan
                @can('branches')
                    <x-sidebar-list-item :title="'Branches'" :icon="'ri-store-2-line'" :route="'admin.branches.index'" />
                @endcan
                @can('reservations')
                    <x-sidebar-list-item :title="'Reservations'" :icon="'ri-calendar-check-line'" :route="'admin.reservations.index'" />
                @endcan
            </ul>
        @endcanany

        @canany(['loyalty', 'gift-cards'])
            <div class="sidebar-section">Marketing &amp; Growth</div>
            <ul class="sidebar-list">
                @can('loyalty')
                    <x-sidebar-list-item :title="'Loyalty Program'" :icon="'ri-gift-line'" :route="'admin.loyalty.index'" />
                @endcan
                @can('gift-cards')
                    <x-sidebar-list-item :title="'Gift Cards'" :icon="'ri-coupon-3-line'" :route="'admin.gift-cards.index'" />
                @endcan
            </ul>
        @endcanany

        @canany(['customers', 'employees'])
            <div class="sidebar-section">People</div>
            <ul class="sidebar-list">
                @can('customers')
                    <x-sidebar-list-item :title="'Customers'" :icon="'ri-team-line'" :route="'admin.customers.index'" />
                @endcan
                @can('employees')
                    <x-sidebar-list-item :title="'Employees'" :icon="'ri-user-star-line'" :route="'admin.employees.index'" />
                @endcan
            </ul>
        @endcanany

        @can('reports')
            <div class="sidebar-section">Reports</div>
            <ul class="sidebar-list">
                <x-sidebar-list-item :title="'Report'" :icon="'ri-bar-chart-2-line'" :route="'admin.report.index'" />
            </ul>
        @endcan

        @can('settings')
            <div class="sidebar-section">System</div>
            <ul class="sidebar-list">
                <x-sidebar-list-item :title="'Settings'" :icon="'ri-settings-3-line'" :route="'admin.settings.index'" />
            </ul>
        @endcan
    </nav>

    <div class="border-t border-slate-200 p-3 shrink-0">
        <a href="{{ route('logout') }}" class="sidebar-link text-slate-600 hover:text-red-600">
            <i class="ri-logout-box-r-line"></i>
            <span>Logout</span>
        </a>
    </div>
</div>


