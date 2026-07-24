@php
    $isSupplier = is_supplier();
@endphp
<div class="flex flex-col h-full">
    <a class="sidebar-brand" href="{{ route($isSupplier ? 'supplier.dashboard' : 'seller.dashboard') }}">
        <span class="flex items-center justify-center h-9 w-9 rounded-lg bg-brand-600 text-white">
            <i class="ri-restaurant-2-line text-lg"></i>
        </span>
        <div class="flex flex-col leading-tight">
            <span class="text-sm font-semibold text-slate-900">{{ config('app.name', env('APP_NAME')) }}</span>
            <span class="text-[10px] uppercase tracking-wider text-slate-400">{{ $isSupplier ? 'Supplier' : 'Seller' }}</span>
        </div>
    </a>

    <nav class="flex-1 overflow-y-auto pb-4">
        @if(is_seller())
            <div class="sidebar-section">Main</div>
            <x-sidebar-list-item :title="'Dashboard'" :icon="'ri-dashboard-line'" :route="'seller.dashboard'" />
            <x-sidebar-list-item :title="'POS'" :icon="'ri-shopping-cart-2-line'" :route="'seller.pos.index'" />

            <div class="sidebar-section">Inventory</div>
            <x-sidebar-list-item :title="'Products'" :icon="'ri-box-3-line'" :route="'seller.products.index'" />
            <x-sidebar-list-item :title="'Stock History'" :icon="'ri-stock-line'" :route="'seller.stocks.index'" />

            <div class="sidebar-section">Operations</div>
            <x-sidebar-list-item :title="'Sales'" :icon="'ri-file-paper-2-line'" :route="'seller.sales.index'" />
            <x-sidebar-list-item :title="'Kitchen Display'" :icon="'ri-tablet-line'" :route="'seller.kds.index'" />
            <x-sidebar-list-item :title="'Floors'" :icon="'ri-building-line'" :route="'seller.floors.index'" />
            <x-sidebar-list-item :title="'Dining Tables'" :icon="'ri-reserved-line'" :route="'seller.diningTables.index'" />
            <x-sidebar-list-item :title="'Reservations'" :icon="'ri-calendar-check-line'" :route="'seller.reservations.index'" />
            <x-sidebar-list-item :title="'Customers'" :icon="'ri-team-line'" :route="'seller.customers.index'" />
            <x-sidebar-list-item :title="'Employees'" :icon="'ri-user-star-line'" :route="'seller.employees.index'" />

            <div class="sidebar-section">Reports</div>
            <x-sidebar-list-item :title="'Report'" :icon="'ri-bar-chart-2-line'" :route="'seller.report.index'" />

            <div class="sidebar-section">System</div>
            <x-sidebar-list-item :title="'Settings'" :icon="'ri-settings-3-line'" :route="'seller.settings.index'" />
        @endif

        @if($isSupplier)
            <div class="sidebar-section">Main</div>
            <x-sidebar-list-item :title="'Dashboard'" :icon="'ri-dashboard-line'" :route="'supplier.dashboard'" />
            <x-sidebar-list-item :title="'Supply POS'" :icon="'ri-truck-line'" :route="'supplier.supply.index'" />

            <div class="sidebar-section">Inventory</div>
            <x-sidebar-list-item :title="'Products'" :icon="'ri-box-3-line'" :route="'supplier.products.index'" />
            <x-sidebar-list-item :title="'Stock History'" :icon="'ri-stock-line'" :route="'supplier.stocks.index'" />

            <div class="sidebar-section">Operations</div>
            <x-sidebar-list-item :title="'Invoices'" :icon="'ri-file-list-3-line'" :route="'supplier.invoices'" />
            <x-sidebar-list-item :title="'Customers'" :icon="'ri-team-line'" :route="'supplier.customers.index'" />

            <div class="sidebar-section">Reports</div>
            <x-sidebar-list-item :title="'Report'" :icon="'ri-bar-chart-2-line'" :route="'supplier.report.index'" />

            <div class="sidebar-section">System</div>
            <x-sidebar-list-item :title="'Settings'" :icon="'ri-settings-3-line'" :route="'supplier.settings.index'" />
        @endif
    </nav>

    <div class="border-t border-slate-200 p-3 shrink-0">
        <a href="{{ route('logout') }}" class="sidebar-link text-slate-600 hover:text-red-600">
            <i class="ri-logout-box-r-line"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
