@props([
    'subtotal' => 0,
    'totalPrice' => 0,
    'diningTables',
    'employees',
    'cart',
    'sale' => null,
    'saleItems' => [],
    'isMobile' => false,
    'vatConfig' => ['mode' => 'disabled', 'rate' => 0],
    'discountConfig' => ['enabled' => false, 'rate' => 0, 'type' => 'percentage'],
])
@php $isSale = request('sale'); @endphp

<div class="flex flex-col h-full bg-white select-none">
    {{-- Header / Order Metadata --}}
    <div class="p-2.5 border-b border-slate-200 bg-slate-50/70 shrink-0 space-y-2">
        {{-- Order Mode: Dine-In (table order) vs Counter (normal POS order) --}}
        @if ($isSale && $sale)
            @php
                $saleTypeValue = $sale?->order_type instanceof \App\Enums\OrderType
                    ? $sale->order_type->value
                    : ($sale?->order_type ?? 'dine_in');
                $saleTypeLabel = ($sale?->order_type instanceof \App\Enums\OrderType)
                    ? $sale->order_type->label()
                    : ucfirst((string) $saleTypeValue);
            @endphp
            <div class="flex items-center justify-center gap-1.5 rounded-lg bg-slate-100 border border-slate-200 px-2 py-1 text-xs font-extrabold text-slate-700 uppercase tracking-wider">
                <i class="ri-lock-line text-slate-400"></i> {{ $saleTypeLabel }}
            </div>
        @else
            <div class="grid grid-cols-2 gap-1 p-0.5 rounded-lg bg-slate-100 border border-slate-200" role="tablist" aria-label="Order mode">
                <button type="button" data-order-mode-btn="dine_in" onclick="window.setOrderMode('dine_in')"
                    class="inline-flex items-center justify-center gap-1.5 rounded-md px-2 py-1 text-xs font-extrabold text-slate-500 transition">
                    <i class="ri-restaurant-line"></i> {{ __('admin.pos.mode_dine_in') }}
                </button>
                <button type="button" data-order-mode-btn="counter" onclick="window.setOrderMode('counter')"
                    class="inline-flex items-center justify-center gap-1.5 rounded-md px-2 py-1 text-xs font-extrabold text-slate-500 transition">
                    <i class="ri-store-3-line"></i> {{ __('admin.pos.mode_counter') }}
                </button>
            </div>
        @endif

        {{-- Table & Staff Row (table only matters for dine-in) --}}
        <div class="grid grid-cols-2 gap-2">
            <div class="js-table-wrap">
                <select id="tableSelect"
                    class="w-full bg-white border border-slate-200 rounded-lg px-2 py-1 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    required>
                    <option value="">{{ __('admin.pos.table') }}</option>
                    @php $cartTable = ($sale?->getRelationValue('diningTable') ?? $sale?->getRelationValue('table')) ?? $sale?->diningTable ?? $sale?->table ?? null; @endphp
                    @if ($isSale && $cartTable)
                        <option value="{{ $cartTable->id }}" selected>{{ $cartTable->name }}</option>
                    @endif
                    @foreach ($diningTables as $table)
                        @php $cartTblStatus = $table->status instanceof \App\Enums\TableStatus ? $table->status : \App\Enums\TableStatus::tryFrom((string) $table->status); @endphp
                        @if ($cartTblStatus !== \App\Enums\TableStatus::OCCUPIED)
                            <option value="{{ $table->id }}">{{ $table->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <select id="employeeSelect"
                class="w-full bg-white border border-slate-200 rounded-lg px-2 py-1 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500"
                required>
                <option value="">{{ __('admin.pos.server') }}</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Order Items List --}}
    <div class="flex-1 overflow-y-auto p-2.5 min-h-0">
        <div class="flex items-center justify-between mb-1.5 pb-1 border-b border-slate-100">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                <i class="ri-shopping-basket-line text-sm text-orange-600"></i> {{ __('admin.pos.selected_items') }}
            </h3>
            <span class="text-[11px] font-bold text-slate-400" id="itemsCount"></span>
        </div>

        <div id="cart" class="space-y-1.5">
            @if ($isSale)
                @forelse ($saleItems as $item)
                    <x-pos.sale-item :item="$item" />
                @empty
                    <div class="empty-state py-12 px-4 text-center">
                        <div
                            class="h-14 w-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                            <i class="ri-shopping-cart-2-line"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-700">{{ __('admin.pos.order_is_empty') }}</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">{{ __('admin.pos.empty_desc') }}</p>
                    </div>
                @endforelse
            @else
                @forelse ($cart->items as $item)
                    <x-pos.cart-item :item="$item" />
                @empty
                    <div class="empty-state py-12 px-4 text-center">
                        <div
                            class="h-14 w-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                            <i class="ri-shopping-cart-2-line"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-700">{{ __('admin.pos.order_is_empty') }}</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">{{ __('admin.pos.empty_desc') }}</p>
                    </div>
                @endforelse
            @endif
        </div>
    </div>

    {{-- Billing & Checkout Settlement Footer --}}
    <div class="border-t border-slate-200 p-2.5 space-y-2 bg-slate-50 shrink-0">
        {{-- Subtotal --}}
        <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
            <span>{{ __('admin.pos.subtotal') }}</span>
            <div class="flex items-baseline gap-1 text-slate-900 font-bold text-sm">
                <span class="text-xs font-normal text-slate-400">৳</span>
                <span id="subtotal">{{ $subtotal }}</span>
            </div>
        </div>

        {{-- Discount (auto-applied from Settings; values by live totals JS) --}}
        @php
            $discountType = ($discountConfig['type'] ?? 'percentage') === 'flat' ? 'flat' : 'percentage';
            $discountRate = (float) ($discountConfig['rate'] ?? 0);
            $discountRateLabel = rtrim(rtrim(number_format($discountRate, 2), '0'), '.');
        @endphp
        <div class="flex items-center justify-between text-xs font-semibold text-emerald-700" id="globalDiscountRow"
            data-discount-type="{{ $discountType }}"
            @if(empty($discountConfig['enabled']) || $discountRate <= 0) style="display:none" @endif>
            <span id="globalDiscountLabel">{{ $discountType === 'flat' ? "Discount (৳{$discountRateLabel})" : "Discount ({$discountRateLabel}%)" }}</span>
            <div class="flex items-baseline gap-1 font-bold text-sm">
                <span class="text-xs font-normal">-</span>
                <span id="globalDiscountAmount">0</span>
            </div>
        </div>

        {{-- VAT calculation (mode/rate driven by Settings; values by live totals JS) --}}
        <div class="flex items-center justify-between text-xs font-semibold text-slate-600" id="vatRow">
            <span id="vatLabel" data-exclusive="{{ __('admin.pos.vat_added') }}" data-inclusive="{{ __('admin.pos.vat_included') }}" data-disabled="{{ __('admin.pos.vat_off') }}">{{ __('admin.pos.vat_off') }}</span>
            <div class="flex items-baseline gap-1 text-slate-900 font-bold text-sm">
                <span class="text-xs font-normal text-slate-400" id="vatSign">+</span>
                <span id="vatAmount">0</span>
            </div>
        </div>

        {{-- Discount & Paid Row --}}
        <div class="grid grid-cols-2 gap-2">
            <div>
                <div class="flex items-center justify-between mb-0.5">
                    <label class="text-[11px] font-bold text-slate-500">{{ __('admin.pos.discount') }}</label>
                    <select id="discountTypeSelect"
                        class="discountTypeSelect text-[10px] font-extrabold text-slate-600 bg-white border border-slate-200 rounded-md px-1 py-px focus:outline-none focus:ring-1 focus:ring-orange-500 cursor-pointer"
                        title="Discount type">
                        <option value="flat">৳ Flat</option>
                        <option value="percentage">% Pct</option>
                    </select>
                </div>
                <div class="relative">
                    <span class="discountPrefix absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">৳</span>
                    <input type="number" id="discountInput"
                        class="w-full bg-white border border-slate-200 rounded-lg pl-6 pr-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500"
                        value="0" min="0" step="0.01">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-0.5">{{ __('admin.pos.paid_amount') }}</label>
                <div class="relative">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">৳</span>
                    <input type="number" id="paidInput"
                        class="w-full bg-white border border-slate-200 rounded-lg pl-6 pr-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500"
                        value="0" min="0" step="0.01">
                </div>
            </div>
        </div>

        {{-- Digital Amount Summary Screen --}}
        <div class="rounded-xl p-2.5 bg-slate-950 text-white shadow-xl shadow-slate-950/20 border border-slate-800">
            <div class="flex items-baseline justify-between">
                <span class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400">{{ __('admin.pos.total_payable') }}</span>
                <div class="flex items-baseline gap-1">
                    <span class="text-xs font-bold text-amber-300">৳</span>
                    <span class="text-xl font-black tracking-tight" id="totalPrice">{{ $totalPrice }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between text-xs mt-1.5 pt-1.5 border-t border-white/10">
                <span class="text-slate-400 font-medium">{{ __('admin.pos.due_balance') }}</span>
                <div class="flex items-baseline gap-0.5 font-bold text-red-300">
                    <span>৳</span>
                    <span id="due">{{ $isSale && $sale ? $sale->due : 0 }}</span>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="grid grid-cols-2 gap-1.5 pt-0.5">
            @if ($isSale)
                <button type="button" id="updateSaleBtn"
                    class="col-span-2 bg-orange-600 hover:bg-orange-700 text-white font-extrabold py-2 px-4 rounded-lg text-sm transition shadow-lg shadow-orange-600/25 active:scale-[0.98] flex items-center justify-center gap-2"
                    onclick="window.updateSale()">
                    <i class="ri-check-double-line text-base"></i> {{ __('admin.pos.update_sale_order') }}
                </button>
            @else
                <button type="button" id="holdBtn"
                    class="bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300 font-bold py-2 px-3 rounded-lg text-xs sm:text-sm transition active:scale-[0.98] flex items-center justify-center gap-1.5 shadow-sm"
                    onclick="window.hold()">
                    <i class="ri-pause-circle-line text-base"></i> {{ __('admin.pos.hold_order') }}
                </button>
                <button type="button" id="checkoutBtn"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold py-2 px-3 rounded-lg text-xs sm:text-sm transition shadow-lg shadow-emerald-600/25 active:scale-[0.98] flex items-center justify-center gap-1.5"
                    onclick="window.checkout()">
                    <i class="ri-shopping-bag-3-line text-base"></i> {{ __('admin.pos.checkout') }}
                </button>
            @endif
        </div>
    </div>
</div>
