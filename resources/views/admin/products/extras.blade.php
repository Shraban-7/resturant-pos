@extends('layouts.admin')
@section('title', 'Dish Extras')
@section('page_title', 'Dish Extras')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<a href="{{ route('admin.products.index') }}">Menu Items</a>
<span class="separator">/</span>
<span class="current">Extras</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Priced extras for <strong>{{ $product->name }}</strong> — e.g. Extra Cheese. Guests tick them in the POS modal.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-secondary">
            <i class="ri-arrow-left-line"></i> Back to Item
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-slate-800">Attached to this dish</h3>
        </div>
        <div class="table-wrap border-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Extra</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($product->extras as $extra)
                        <tr>
                            <td class="font-medium text-slate-800">{{ $extra->name }}</td>
                            <td class="text-right font-medium">{{ money($extra->price) }}</td>
                            <td class="text-right">
                                <form action="{{ route('admin.products.extras.destroy', [$product, $extra]) }}" method="post" class="inline"
                                      onsubmit="return confirm('Remove this extra from the dish?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm text-red-600" title="Remove">
                                        <i class="ri-unlink"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="empty-state py-8">
                                    <i class="ri-coins-line"></i>
                                    <h3>No extras yet</h3>
                                    <p>Attach priced additions guests can add to this dish.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-slate-800">Attach an extra</h3>
        </div>
        <div class="card-body">
            @if ($availableAdditions->isEmpty())
                <p class="text-sm text-slate-500">No other active additions to attach, or all are already attached. <a href="{{ route('admin.additions.index') }}" class="text-orange-600 font-semibold">Manage additions</a>.</p>
            @else
                <form action="{{ route('admin.products.extras.store', $product) }}" method="post" class="space-y-3">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Addition</label>
                        <select name="addition_id" class="form-select form-control" required>
                            <option value="">Select addition</option>
                            @foreach ($availableAdditions as $addition)
                                <option value="{{ $addition->id }}">
                                    {{ $addition->name }} ({{ money($addition->price) }})
                                </option>
                            @endforeach
                        </select>
                        <p class="form-hint">Only active additions can be attached.</p>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Attach Extra</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

@endsection
