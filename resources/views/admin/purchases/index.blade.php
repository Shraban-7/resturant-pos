@extends('layouts.admin')
@section('title', 'Purchases')
@section('page_title', 'Ingredient Purchases')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Purchases</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Total spent: <span class="font-bold text-slate-900">{{ money($totalSpent) }}</span></p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary"><i class="ri-truck-line"></i> Suppliers</a>
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'addPurchase' })">
            <i class="ri-add-line"></i> New Purchase
        </button>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Ingredient</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr>
                        <td class="text-slate-500">{{ $purchase->purchase_date?->format('d M Y') }}</td>
                        <td class="font-medium text-slate-800">{{ $purchase->supplier?->name ?? '-' }}</td>
                        <td>
                            {{ $purchase->product?->name ?? '-' }}
                            @if ($purchase->note)
                                <span class="block text-xs text-slate-400">{{ $purchase->note }}</span>
                            @endif
                        </td>
                        <td class="text-right">{{ rtrim(rtrim(number_format($purchase->quantity, 3), '0'), '.') }} {{ $purchase->product?->unit?->short_name }}</td>
                        <td class="text-right">{{ money($purchase->buying_price) }}</td>
                        <td class="text-right font-semibold">{{ money($purchase->total_price) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500 py-8">
                            No purchases yet. Record what you buy from suppliers to grow ingredient stock.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($purchases->hasPages())
        <div class="card-footer">{{ $purchases->links() }}</div>
    @endif
</div>

{{-- Add --}}
<div x-data="{ open: false }"
     @open-modal.window="if ($event.detail.id === 'addPurchase') open = true"
     x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-5" @click.outside="open = false">
        <h3 class="text-lg font-semibold mb-4">New Purchase</h3>
        <form method="post" action="{{ route('admin.purchases.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select" required>
                    <option value="">Select supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Raw ingredient</label>
                <select name="product_id" class="form-select" required>
                    <option value="">Select ingredient</option>
                    @foreach ($ingredients as $ingredient)
                        <option value="{{ $ingredient->id }}">{{ $ingredient->name }} (in stock: {{ $ingredient->stock_in - $ingredient->stock_out }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.001" min="0.001" name="quantity" class="form-control" required>
                </div>
                <div>
                    <label class="form-label">Rate (BDT)</label>
                    <input type="number" min="0" name="buying_price" class="form-control" required>
                </div>
                <div>
                    <label class="form-label">Date</label>
                    <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
            </div>
            <div>
                <label class="form-label">Note (optional)</label>
                <input type="text" name="note" class="form-control">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Save & Add Stock</button>
            </div>
        </form>
    </div>
</div>

@endsection
