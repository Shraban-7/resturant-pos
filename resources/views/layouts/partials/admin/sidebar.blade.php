<div class="flex flex-col h-full">
    <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
        @if (store_logo_url())
            <img src="{{ store_logo_url() }}" alt="{{ store_name() }}" class="h-10 w-10 rounded-xl object-cover bg-white/10 shrink-0">
        @else
            <span class="flex items-center justify-center h-10 w-10 rounded-xl bg-brand-600 text-white shadow-lg shadow-brand-900/40">
                <i class="ri-restaurant-2-line text-xl"></i>
            </span>
        @endif
        <div class="flex flex-col leading-tight min-w-0">
            <span class="text-[0.95rem] font-bold text-white tracking-tight truncate">{{ store_name() }}</span>
            <span class="text-[10px] uppercase tracking-[0.14em] text-slate-400">{{ is_employee() ? __('admin.common.employee') : __('admin.common.admin') }}</span>
        </div>
    </a>

    <nav class="flex-1 overflow-y-auto pb-4">
<div class="sidebar-section">{{ __('admin.sidebar.main') }}</div>
        <ul class="sidebar-list">
            @can('dashboard')
                <x-sidebar-list-item :title="__('admin.sidebar.dashboard')" :icon="'ri-dashboard-line'" :route="'admin.dashboard'" />
            @endcan
            @can('pos')
                <x-sidebar-list-item :title="__('admin.sidebar.pos')" :icon="'ri-shopping-cart-2-line'" :route="'admin.pos.index'" />
            @endcan
        </ul>

        @canany(['products', 'stocks'])
            <div class="sidebar-section">{{ __('admin.sidebar.inventory') }}</div>
            <ul class="sidebar-list">
                @can('products')
                    <x-sidebar-list-item :title="__('admin.sidebar.products')" :icon="'ri-box-3-line'" :route="'admin.products.index'" />
                    <x-sidebar-list-item :title="__('admin.sidebar.suppliers')" :icon="'ri-truck-line'" :route="'admin.suppliers.index'" />
                    <x-sidebar-list-item :title="__('admin.sidebar.purchases')" :icon="'ri-shopping-basket-line'" :route="'admin.purchases.index'" />
                @endcan
                @can('stocks')
                    <x-sidebar-list-item :title="__('admin.sidebar.stock_history')" :icon="'ri-stock-line'" :route="'admin.stocks.index'" />
                @endcan
            </ul>
        @endcanany

        @canany(['sales', 'kds', 'floors', 'branches', 'reservations'])
            <div class="sidebar-section">{{ __('admin.sidebar.operations') }}</div>
            <ul class="sidebar-list">
                @can('sales')
                    <x-sidebar-list-item :title="__('admin.sidebar.sales')" :icon="'ri-file-paper-2-line'" :route="'admin.sales.index'" />
                @endcan
                @can('kds')
                    <x-sidebar-list-item :title="__('admin.sidebar.kitchen_display')" :icon="'ri-tablet-line'" :route="'admin.kds.index'" />
                @endcan
                @can('floors')
                    <x-sidebar-list-item :title="__('admin.sidebar.floors')" :icon="'ri-building-line'" :route="'admin.floors.index'" />
                    <x-sidebar-list-item :title="__('admin.sidebar.dining_tables')" :icon="'ri-reserved-line'" :route="'admin.diningTables.index'" />
                @endcan
                @can('branches')
                    <x-sidebar-list-item :title="__('admin.sidebar.branches')" :icon="'ri-store-2-line'" :route="'admin.branches.index'" />
                @endcan
                @can('reservations')
                    <x-sidebar-list-item :title="__('admin.sidebar.reservations')" :icon="'ri-calendar-check-line'" :route="'admin.reservations.index'" />
                @endcan
            </ul>
        @endcanany

        @canany(['loyalty', 'gift-cards'])
            <div class="sidebar-section">{{ __('admin.sidebar.marketing') }}</div>
            <ul class="sidebar-list">
                @can('loyalty')
                    <x-sidebar-list-item :title="__('admin.sidebar.loyalty_program')" :icon="'ri-gift-line'" :route="'admin.loyalty.index'" />
                @endcan
                @can('gift-cards')
                    <x-sidebar-list-item :title="__('admin.sidebar.gift_cards')" :icon="'ri-coupon-3-line'" :route="'admin.gift-cards.index'" />
                @endcan
            </ul>
        @endcanany

        @canany(['customers', 'employees'])
            <div class="sidebar-section">{{ __('admin.sidebar.people') }}</div>
            <ul class="sidebar-list">
                @can('customers')
                    <x-sidebar-list-item :title="__('admin.sidebar.customers')" :icon="'ri-team-line'" :route="'admin.customers.index'" />
                @endcan
                @can('employees')
                    <x-sidebar-list-item :title="__('admin.sidebar.employees')" :icon="'ri-user-star-line'" :route="'admin.employees.index'" />
                @endcan
            </ul>
        @endcanany

        @can('reports')
            <div class="sidebar-section">{{ __('admin.sidebar.reports_section') }}</div>
            <ul class="sidebar-list">
                <x-sidebar-list-item :title="__('admin.sidebar.report')" :icon="'ri-bar-chart-2-line'" :route="'admin.report.index'" />
            </ul>
        @endcan

        @can('settings')
            <div class="sidebar-section">{{ __('admin.sidebar.system') }}</div>
            <ul class="sidebar-list">
                <x-sidebar-list-item :title="__('admin.sidebar.settings')" :icon="'ri-settings-3-line'" :route="'admin.settings.index'" />
                <x-sidebar-list-item :title="__('admin.sidebar.storefront')" :icon="'ri-global-line'" :route="'admin.storefront-settings.index'" />
            </ul>
        @endcan
    </nav>

    <div class="border-t border-white/10 p-3 shrink-0">
        <a href="{{ route('logout') }}" class="sidebar-link !text-slate-400 hover:!text-red-300">
            <i class="ri-logout-box-r-line"></i>
            <span>{{ __('admin.sidebar.logout') }}</span>
        </a>
    </div>
</div>


