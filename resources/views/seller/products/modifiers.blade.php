@extends('layouts.admin')
@section('title', 'Product Modifiers')
@section('page_title', 'Product Modifiers')
@section('breadcrumb')
<a href="{{ route('seller.dashboard') }}">Home</a>
<span class="separator">/</span>
<a href="{{ route('seller.products.index') }}">Products</a>
<span class="separator">/</span>
<span class="current">Modifiers</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Configure add-ons and variants for <strong>{{ $product->name }}</strong></p>
    </div>
    <div class="page-actions">
        <a href="{{ route('seller.products.edit', $product) }}" class="btn btn-secondary">
            <i class="ri-arrow-left-line"></i> Back to Product
        </a>
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'createModifier' })">
            <i class="ri-add-line"></i> Create & Attach
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-slate-800">Attached to this product</h3>
        </div>
        <div class="table-wrap border-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Required</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($product->productModifiers as $pm)
                        <tr>
                            <td><span class="badge badge-light">{{ $pm->modifier->group_name }}</span></td>
                            <td class="font-medium">{{ $pm->modifier->name }}</td>
                            <td>{{ money($pm->modifier->price) }}</td>
                            <td>
                                <form action="{{ route('seller.products.modifiers.update', [$product, $pm]) }}" method="post" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="is_required" value="1" class="rounded border-slate-300"
                                               {{ $pm->is_required ? 'checked' : '' }}
                                               onchange="this.form.submit()">
                                        Required
                                    </label>
                                </form>
                            </td>
                            <td class="text-right">
                                <form action="{{ route('seller.products.modifiers.destroy', [$product, $pm]) }}" method="post" class="inline"
                                      onsubmit="return confirm('Remove this modifier from the product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm text-red-600">
                                        <i class="ri-unlink"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state py-8">
                                    <i class="ri-list-settings-line"></i>
                                    <h3>No modifiers attached</h3>
                                    <p>Attach an existing modifier or create a new one.</p>
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
            <h3 class="font-semibold text-slate-800">Attach existing modifier</h3>
        </div>
        <div class="card-body">
            @if ($availableModifiers->isEmpty())
                <p class="text-sm text-slate-500">All active modifiers are already attached, or none exist yet.</p>
            @else
                <form action="{{ route('seller.products.modifiers.store', $product) }}" method="post" class="space-y-3">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Modifier</label>
                        <select name="modifier_id" class="form-select form-control" required>
                            <option value="">Select modifier</option>
                            @foreach ($availableModifiers as $modifier)
                                <option value="{{ $modifier->id }}">
                                    [{{ $modifier->group_name }}] {{ $modifier->name }} (+{{ money($modifier->price) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_required" value="1" class="rounded border-slate-300">
                        Required at POS
                    </label>
                    <div>
                        <button type="submit" class="btn btn-primary">Attach</button>
                    </div>
                </form>
            @endif

            @if ($allModifiers->isNotEmpty())
                <div class="mt-6 border-t border-slate-200 pt-4">
                    <h4 class="text-sm font-semibold text-slate-700 mb-2">Your modifier catalog</h4>
                    <div class="space-y-3 max-h-64 overflow-y-auto text-sm">
                        @foreach ($allModifiers as $group => $mods)
                            <div>
                                <div class="text-xs uppercase tracking-wider text-slate-400 mb-1">{{ $group }}</div>
                                <ul class="space-y-1">
                                    @foreach ($mods as $mod)
                                        <li class="flex justify-between gap-2 text-slate-700">
                                            <span>{{ $mod->name }}</span>
                                            <span class="text-slate-500">{{ money($mod->price) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div x-data="{ open: false }"
     @open-modal.window="if ($event.detail && $event.detail.id === 'createModifier') open = true"
     @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="modal-backdrop" @click="open = false"></div>
            <div class="modal-dialog modal-sm relative">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Modifier</h5>
                        <button type="button" class="text-slate-500" @click="open = false"><i class="ri-close-line text-xl"></i></button>
                    </div>
                    <form action="{{ route('seller.products.modifiers.store', $product) }}" method="post">
                        @csrf
                        <div class="modal-body space-y-3">
                            <div class="form-group">
                                <label class="form-label">Group</label>
                                <input name="group_name" type="text" class="form-control" required placeholder="e.g. Size, Extras">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input name="name" type="text" class="form-control" required placeholder="e.g. Extra Cheese">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Price</label>
                                <input name="price" type="number" step="0.01" min="0" class="form-control" value="0">
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" name="is_required" value="1" class="rounded border-slate-300">
                                Required on this product
                            </label>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create & Attach</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>

@endsection
