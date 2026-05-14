@extends('layouts.admin')
@section('title', 'Update Stock')
@section('page_title', 'Update Stock')
@section('breadcrumb')
<a href="{{ route('seller.stocks.index') }}">Stock History</a>
<span class="separator">/</span>
<span class="current">Update</span>
@endsection

@section('content')

<form action="{{ route('seller.stocks.update') }}" method="POST">
    @csrf
    <div class="max-w-3xl">
        <div class="card">
            <div class="card-header">
                <div>
                    <h6 class="card-title">Stock Update</h6>
                    <p class="card-subtitle">Increase the stock of a product and adjust prices</p>
                </div>
            </div>
            <div class="card-body"
                 x-data="{
                    selected: null,
                    update() {
                        const opt = this.selected && this.selected.options[this.selected.selectedIndex];
                        if (!opt) return;
                        document.getElementById('productUnitSpan').textContent = opt.dataset.unit || '';
                        document.getElementById('buyingPrice').value = opt.dataset.buyingPrice || '';
                        document.getElementById('sellingPrice').value = opt.dataset.sellingPrice || '';
                    }
                 }"
                 x-init="selected = $refs.select; update()">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="form-label">Product</label>
                        <select name="product_id" id="productSelect" x-ref="select" class="form-select" required @change="update()">
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}"
                                        data-unit="{{ $product->unit->short_name }}"
                                        data-buying-price="{{ $product->buying_price }}"
                                        data-selling-price="{{ $product->selling_price }}">
                                    {{ $product->name }} (B: {{ money($product->buying_price) }} | S: {{ money($product->selling_price) }} | Stock: {{ $product->availableStock }} {{ $product->unit->short_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Quantity</label>
                        <div class="input-group">
                            <input type="text" name="stock_in" class="form-control" placeholder="Ex: 100" required>
                            <span class="input-group-text" id="productUnitSpan">Product Unit</span>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Buying Price</label>
                        <div class="input-group">
                            <span class="input-group-text">BDT</span>
                            <input type="text" name="buying_price" id="buyingPrice" class="form-control" placeholder="1000">
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Selling Price</label>
                        <div class="input-group">
                            <span class="input-group-text">BDT</span>
                            <input type="text" name="selling_price" id="sellingPrice" class="form-control" placeholder="1500">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <a href="{{ route('seller.stocks.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line"></i> Update Stock
                </button>
            </div>
        </div>
    </div>
</form>

@endsection
