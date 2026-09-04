@extends('layouts.app')
@section('title', 'POS')

@section('content')

<div class="row mt-3 px-3">
    <div class="col-12 col-lg-8">
        <form>
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Product Name" name="product_name" value="{{ request()->product_name }}">
                <button type="submit" class="btn btn-primary">search</button>
            </div>
        </form>
        
        <div class="row overflow-auto" style="height: 78vh;">
            @foreach ($products as $product)
                <div class="col-6 col-sm-4 col-md-3 mb-3">
                    <div class="card" style="width: 12rem;">
                        <img src="{{ asset($product->image) }}" class="card-img-top" alt="product-img" height="140">
                        <div class="card-body p-2">
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="card-title mb-0 h6">{{ ucwords($product->name) }}</span> <small>({{ $product->stock_in - $product->stock_out }})</small>
                                        </div>
                                        <h6 class="card-title">{{ currency() }} {{ $product->price }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.add_to_cart', $product->id) }}" class="btn btn-outline-dark btn-sm btn-block">Add</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-12 col-lg-4 px-2">
        <div class="border bg-white p-4 h-100 rounded">
            <h4>Order Details</h4>
            @if($sale)
                <label>Items</label>
                <ul class="list-group">
                    @foreach ($sale->items as $item)
                        <li class="list-group-item p-2">
                            <div class="d-flex justify-content-start">
                                <div class="w-20">
                                    <img src="{{ asset($item->product->image) }}" class="rounded" height="64" width="64" alt="product-img">
                                </div>
                                <div class="mx-2 w-80">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-bold m-0">{{ ucwords($item->product->name) }}</p>
                                        <p class="fw-bold m-0">{{ currency() }} {{ $item->total_price }}</p>
                                    </div>
                                    <div>
                                        <div class="input-group mt-1" >
                                            <a class="btn btn-outline-danger btn-sm" href="{{ route('admin.update_cart', $item->id) }}?type=decrement">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <input type="text" class="form-control form-control-sm" value="{{ $item->quantity }}">
                                            <a class="btn btn-outline-success btn-sm" href="{{ route('admin.update_cart', $item->id) }}?type=increment">
                                                <i class="bi bi-plus-lg"></i>
                                            </a>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>            
                <div class="p-3 mt-2 rounded border">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Subtotal</span>
                        <span class="fw-bold">{{ currency() }} <span id="subtotal">{{ $sale->subtotal }}</span></span>
                    </div>
                    <form action="{{ route('admin.chekout', $sale->id) }}" target="_blank" method="POST">
                        @CSRF
                        <div class="mb-3">
                            <label>Select Customer</label>
                            <select class="form-select" name="customer_id">
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} | {{ $customer->phone }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Paid</label>
                                <input type="text" name="paid_amount" class="form-control" id="paid_input" required onkeyup="updateDue()">
                            </div>
                            <div class="col-md-6">
                                <label>Due</label>
                                <input type="text" class="form-control" disabled id="due_amount">
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-primary" type="submit">Checkout</button>
                        </div>
                    </form>

                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function updateDue() {
        var dueAmount = 0;
        var paidAmount = 0;
        var paidAmountInput = document.getElementById('paid_input');
        var subtotalAmount = document.getElementById('subtotal');
        
        subtotalAmount = parseInt(subtotalAmount.innerHTML);    

        paidAmount = parseInt(paidAmountInput.value);
        dueAmount = subtotalAmount - paidAmount;
        if(paidAmount) {
            document.getElementById('due_amount').value = dueAmount;
        }else {
            document.getElementById('due_amount').value = '';
        }
    }
</script>

@endsection
