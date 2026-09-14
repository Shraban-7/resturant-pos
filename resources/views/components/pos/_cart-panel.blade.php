@props([
    'subtotal' => 0,
    'totalPrice' => 0,
    'customers',
    'diningTables',
    'employees',
    'cart',
    'sale' => null,
    'saleItems' => [],
    'isMobile' => false,
])
@php $isSale = request('sale'); @endphp

<div class="flex flex-col h-full bg-white select-none">
    {{-- Header / Order Metadata --}}
    <div class="p-4 border-b border-slate-200 bg-slate-50/70 shrink-0 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="flex items-center justify-center h-8 w-8 rounded-xl bg-orange-600 text-white shadow-sm">
                    <i class="ri-receipt-line"></i>
                </span>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 leading-tight flex items-center gap-1.5">
                        <span>{{ __('admin.pos.current_ticket') }}</span>
                        <span
                            class="text-[11px] font-mono font-bold text-slate-500 bg-slate-200/70 px-2 py-0.5 rounded-md">
                            #{{ $isSale && $sale ? $sale->order_id : $cart->order_id }}
                        </span>
                    </h2>
                </div>
            </div>

            @if ($isSale && $sale)
                <span
                    class="inline-flex items-center gap-1 bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                    <i class="ri-edit-line"></i> {{ __('admin.pos.editing') }}
                </span>
            @endif
        </div>

        {{-- Barcode / Quick code search (Desktop only) --}}
        @if (!$isMobile)
            <div class="relative">
                <i class="ri-barcode-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="productCodeInput"
                    class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-1.5 text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    placeholder="{{ __('admin.pos.code_placeholder') }}">
            </div>
        @endif

        {{-- Customer Select --}}
        <div class="space-y-1.5">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <i class="ri-user-3-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <select id="customerSelect"
                        class="w-full bg-white border border-slate-200 rounded-xl pl-8 pr-3 py-1.5 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500"
                        required>
                        <option value="">{{ __('admin.pos.walk_in') }}</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button"
                    class="h-8 px-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold transition flex items-center gap-1 shadow-sm shrink-0"
                    onclick="window.toggleCustomerForm()" title="{{ __('admin.pos.add_customer') }}">
                    <i class="ri-user-add-line"></i>
                    <span class="hidden sm:inline">{{ __('admin.pos.new_customer') }}</span>
                </button>
            </div>

            <div id="customerForm" class="hidden grid grid-cols-2 gap-2 pt-1">
                <input type="text" id="customer_name"
                    class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white"
                    placeholder="{{ __('admin.pos.customer_name') }}">
                <input type="text" id="customer_phone"
                    class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white"
                    placeholder="{{ __('admin.pos.phone') }}">
            </div>
        </div>

        {{-- Order Type, Table & Staff Row --}}
        <div class="grid grid-cols-3 gap-2">
            <div>
                <select id="orderTypeSelect"
                    class="w-full bg-white border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    onchange="window.toggleOrderType()">
                    @php
                        $saleOrderType =
                            $sale?->order_type instanceof \App\Enums\OrderType
                                ? $sale->order_type->value
                                : $sale?->order_type ?? 'dine_in';
                    @endphp
                    <option value="dine_in"{{ $saleOrderType === 'dine_in' ? ' selected' : '' }}>{{ __('admin.pos.dine_in') }}</option>
                    <option value="takeaway"{{ $saleOrderType === 'takeaway' ? ' selected' : '' }}>{{ __('admin.pos.takeaway') }}</option>
                    <option value="delivery"{{ $saleOrderType === 'delivery' ? ' selected' : '' }}>{{ __('admin.pos.delivery') }}</option>
                </select>
            </div>

            <div id="tableRow" class="col-span-2 grid grid-cols-2 gap-2">
                <select id="tableSelect"
                    class="w-full bg-white border border-slate-200 rounded-xl px-2 py-1.5 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500"
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

                <select id="employeeSelect"
                    class="w-full bg-white border border-slate-200 rounded-xl px-2 py-1.5 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    required>
                    <option value="">{{ __('admin.pos.server') }}</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Order Items List --}}
    <div class="flex-1 overflow-y-auto p-4 min-h-0">
        <div class="flex items-center justify-between mb-2.5 pb-1.5 border-b border-slate-100">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                <i class="ri-shopping-basket-line text-sm text-orange-600"></i> {{ __('admin.pos.selected_items') }}
            </h3>
            <span class="text-[11px] font-bold text-slate-400" id="itemsCount"></span>
        </div>

        <div id="cart" class="space-y-2">
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
    <div class="border-t border-slate-200 p-4 space-y-3 bg-slate-50 shrink-0">
        {{-- Subtotal --}}
        <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
            <span>{{ __('admin.pos.subtotal') }}</span>
            <div class="flex items-baseline gap-1 text-slate-900 font-bold text-sm">
                <span class="text-xs font-normal text-slate-400">৳</span>
                <span id="subtotal">{{ $subtotal }}</span>
            </div>
        </div>

        {{-- Discount & Paid Row --}}
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">{{ __('admin.pos.discount_label') }}</label>
                <div class="relative">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">-</span>
                    <input type="number" id="discountInput"
                        class="w-full bg-white border border-slate-200 rounded-xl pl-6 pr-2 py-1.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500"
                        value="0" min="0" step="0.01">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">{{ __('admin.pos.paid_amount') }}</label>
                <div class="relative">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">৳</span>
                    <input type="number" id="paidInput"
                        class="w-full bg-white border border-slate-200 rounded-xl pl-6 pr-2 py-1.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500"
                        value="0" min="0" step="0.01">
                </div>
            </div>
        </div>

        {{-- Gift Card Verification --}}
        <div class="rounded-xl border border-slate-200 p-2 bg-white space-y-1 shadow-sm">
            <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                <span>{{ __('admin.pos.gift_card_voucher') }}</span>
                <i class="ri-gift-line text-orange-500"></i>
            </div>
            <div class="flex items-center gap-1.5">
                <input type="text" id="giftCardCodeInput"
                    class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 text-xs font-mono font-bold uppercase placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    placeholder="ENTER CODE" maxlength="64" onchange="window.verifyGiftCard?.()"
                    onkeyup="if(event.key==='Enter')this.blur()">
                <button type="button" id="verifyGiftCardBtn"
                    class="px-3 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-700 text-xs font-bold transition flex items-center gap-1"
                    onclick="window.verifyGiftCard?.()">
                    <i class="ri-shield-check-line"></i> {{ __('admin.pos.apply') }}
                </button>
            </div>
            <div id="giftCardStatus" class="text-[10px] text-slate-500 hidden pt-0.5"></div>
        </div>

        {{-- Kitchen Order Note --}}
        <div>
            <input type="text" id="note"
                class="w-full bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500"
                placeholder="{{ __('admin.pos.note_placeholder') }}">
        </div>

        {{-- Digital Amount Summary Screen --}}
        <div class="rounded-2xl p-3.5 bg-slate-950 text-white shadow-xl shadow-slate-950/20 border border-slate-800">
            <div class="flex items-baseline justify-between">
                <span class="text-[11px] uppercase tracking-widest font-extrabold text-slate-400">{{ __('admin.pos.total_payable') }}</span>
                <div class="flex items-baseline gap-1">
                    <span class="text-sm font-bold text-amber-300">৳</span>
                    <span class="text-2xl font-black tracking-tight" id="totalPrice">{{ $totalPrice }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between text-xs mt-2 pt-2 border-t border-white/10">
                <span class="text-slate-400 font-medium">{{ __('admin.pos.due_balance') }}</span>
                <div class="flex items-baseline gap-0.5 font-bold text-red-300">
                    <span>৳</span>
                    <span id="due">{{ $isSale && $sale ? $sale->due : 0 }}</span>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="grid grid-cols-2 gap-2 pt-1">
            @if ($isSale)
                <button type="button" id="updateSaleBtn"
                    class="col-span-2 bg-orange-600 hover:bg-orange-700 text-white font-extrabold py-3 px-4 rounded-xl text-sm transition shadow-lg shadow-orange-600/25 active:scale-[0.98] flex items-center justify-center gap-2"
                    onclick="window.updateSale()">
                    <i class="ri-check-double-line text-lg"></i> {{ __('admin.pos.update_sale_order') }}
                </button>
            @else
                <button type="button" id="holdBtn"
                    class="bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300 font-bold py-3 px-4 rounded-xl text-xs sm:text-sm transition active:scale-[0.98] flex items-center justify-center gap-1.5 shadow-sm"
                    onclick="window.hold()">
                    <i class="ri-pause-circle-line text-base"></i> {{ __('admin.pos.hold_order') }}
                </button>
                <button type="button" id="checkoutBtn"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold py-3 px-4 rounded-xl text-xs sm:text-sm transition shadow-lg shadow-emerald-600/25 active:scale-[0.98] flex items-center justify-center gap-1.5"
                    onclick="window.checkout()">
                    <i class="ri-shopping-bag-3-line text-base"></i> {{ __('admin.pos.checkout') }}
                </button>
            @endif
        </div>
    </div>
</div>
