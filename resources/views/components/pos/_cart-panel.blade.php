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

<div class="p-4 border-b border-slate-200 space-y-3 shrink-0">
    <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
            <i class="ri-shopping-bag-3-line text-brand-600"></i>
            Order
        </h2>
        @if($isSale && $sale)
            <span class="badge badge-warning">
                <i class="ri-edit-box-line"></i> Editing #{{ $sale->order_id }}
            </span>
        @endif
    </div>

    @if(!$isMobile)
        <div class="relative">
            <i class="ri-barcode-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
            <input type="text" id="productCodeInput" class="form-control pl-10" placeholder="Scan code or type name, then Enter">
        </div>
    @endif

    <div class="grid grid-cols-[1fr_auto] gap-2">
        <select id="customerSelect" class="form-select" required>
            <option value="">Select customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-secondary" onclick="window.toggleCustomerForm()" title="Add new customer">
            <i class="ri-user-add-line"></i>
        </button>
    </div>

    <div id="customerForm" class="hidden grid grid-cols-2 gap-2">
        <input type="text" id="customer_name" class="form-control form-control-sm" placeholder="Customer name">
        <input type="text" id="customer_phone" class="form-control form-control-sm" placeholder="Phone">
    </div>

    <div class="grid grid-cols-2 gap-2">
        <select id="tableSelect" class="form-select form-control-sm" required>
            <option value="">Select table</option>
            @if($isSale && $sale?->table)
                <option value="{{ $sale->table->id }}" selected>{{ $sale->table->name }}</option>
            @endif
            @foreach ($diningTables as $table)
                @if ($table->status !== \App\Enums\TableStatus::OCCUPIED)
                    <option value="{{ $table->id }}">{{ $table->name }}</option>
                @endif
            @endforeach
        </select>
        <select id="employeeSelect" class="form-select form-control-sm" required>
            <option value="">Select employee</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="flex-1 overflow-y-auto p-4 min-h-0">
    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
        <i class="ri-list-unordered text-sm"></i> Items
        <span class="ml-auto text-slate-400 font-normal normal-case" id="itemsCount">0</span>
    </h3>
    <div id="cart" class="space-y-2">
        @if($isSale)
            @forelse ($saleItems as $item)
                <x-pos.sale-item :item="$item" />
            @empty
                <div class="empty-state py-10">
                    <i class="ri-shopping-cart-2-line"></i>
                    <h3>No items yet</h3>
                    <p>Click a product to add it to the order.</p>
                </div>
            @endforelse
        @else
            @forelse ($cart->items as $item)
                <x-pos.cart-item :item="$item" />
            @empty
                <div class="empty-state py-10">
                    <i class="ri-shopping-cart-2-line"></i>
                    <h3>No items yet</h3>
                    <p>Click a product to add it to the order.</p>
                </div>
            @endforelse
        @endif
    </div>
</div>

<div class="border-t border-slate-200 p-4 space-y-3 bg-slate-50 shrink-0">
    <div class="flex items-center justify-between text-sm">
        <span class="text-slate-600">Subtotal</span>
        <span class="font-semibold text-slate-900" id="subtotal">{{ $subtotal }}</span>
    </div>
    <div class="grid grid-cols-2 gap-2">
        <label class="block">
            <span class="text-xs text-slate-500 mb-1 block">Discount</span>
            <div class="relative">
                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">-</span>
                <input type="number" id="discountInput" class="form-control form-control-sm pl-6" value="0" min="0" step="0.01">
            </div>
        </label>
        <label class="block">
            <span class="text-xs text-slate-500 mb-1 block">Paid</span>
            <input type="number" id="paidInput" class="form-control form-control-sm" value="0" min="0" step="0.01">
        </label>
    </div>

    <input type="text" id="note" class="form-control form-control-sm" placeholder="Add a note (optional)">

    <div class="bg-white rounded-lg p-3 border border-slate-200">
        <div class="flex items-baseline justify-between">
            <span class="text-sm text-slate-600">Total</span>
            <span class="text-2xl font-bold text-slate-900" id="totalPrice">{{ $totalPrice }}</span>
        </div>
        <div class="flex items-center justify-between text-sm mt-1">
            <span class="text-slate-500">Due</span>
            <span class="font-semibold text-red-600" id="due">{{ $isSale && $sale ? $sale->due : 0 }}</span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2">
        @if($isSale)
            <button type="button" id="updateSaleBtn" class="btn btn-primary col-span-2" onclick="window.updateSale()">
                <i class="ri-check-double-line"></i> Update Order
            </button>
        @else
            <button type="button" id="holdBtn" class="btn btn-secondary" onclick="window.hold()">
                <i class="ri-pause-circle-line"></i> Hold
            </button>
            <button type="button" id="checkoutBtn" class="btn btn-primary" onclick="window.checkout()">
                <i class="ri-shopping-bag-3-line"></i> Checkout
            </button>
        @endif
    </div>
</div>
