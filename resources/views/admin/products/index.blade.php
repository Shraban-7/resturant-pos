@extends('layouts.admin')
@section('title', 'Menu Items')
@section('page_title', 'Menu Items')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Menu Items</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $products->total() }} {{ Str::plural('dish', $products->total()) }} on your menu · stock lives on raw materials</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="{{ route('admin.products.create') }}">
            <i class="ri-add-line"></i> Add Menu Item
        </a>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Recipe</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="{{ $product->imageUrl() }}" alt="image" class="h-12 w-12 object-cover rounded-lg bg-orange-50" />
                                <span class="font-medium text-slate-800">{{ $product->name }}</span>
                                @if ($product->name_bn)
                                    <span class="block text-xs text-slate-500">{{ $product->name_bn }}</span>
                                @endif
                                @if ($product->type === \App\Enums\ProductType::BUFFET)
                                    <span class="badge badge-success" title="Per-person buffet, unlimited">Buffet</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-light">{{ $product->category->name }}</span>
                            @if (!$product->meal_times || $product->meal_times->isEmpty())
                                <span class="badge badge-success" title="Served all day">All day</span>
                            @else
                                @foreach ($product->meal_times as $slot)
                                    @php $slotVal = $slot instanceof \App\Enums\MealSlot ? $slot->value : (string) $slot; $slotLabel = $slot instanceof \App\Enums\MealSlot ? $slot->label() : ucfirst($slotVal); @endphp
                                    <span class="badge badge-light" title="Served at {{ $slotVal }}">{{ $slotLabel }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td class="font-medium">{{ money($product->selling_price) }}</td>
                        <td>
                            @php $ingredientCount = $product->recipe ? $product->recipe->ingredients->count() : 0; @endphp
                            @if ($product->type === \App\Enums\ProductType::DISH)
                                @if ($product->recipe)
                                    <span class="badge badge-success" title="Recipe BOM drives raw-material deduction">{{ $ingredientCount }} ingredients</span>
                                @else
                                    <span class="badge badge-light" title="No recipe — always available, no stock impact">No recipe</span>
                                @endif
                            @else
                                <span class="badge badge-light" title="Buffet never touches inventory">—</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1">
                                @php $prodType = $product->type instanceof \App\Enums\ProductType ? $product->type : \App\Enums\ProductType::tryFrom((string) $product->type); @endphp
                                @if ($prodType === \App\Enums\ProductType::DISH)
                                    <a href="{{ route('admin.products.recipe.edit', $product) }}"
                                       class="btn {{ $product->recipe ? 'btn-success' : 'btn-secondary' }} btn-sm"
                                       title="{{ $product->recipe ? "Recipe BOM ({$ingredientCount} ingredients)" : 'Add recipe BOM' }}">
                                        <i class="ri-flask-line"></i>
                                        @if ($product->recipe)
                                            <span class="text-[10px] font-bold">{{ $ingredientCount }}</span>
                                        @endif
                                    </a>
                                @endif
                                <a href="{{ route('admin.products.modifiers.index', $product) }}" class="btn btn-secondary btn-sm" title="Modifiers">
                                    <i class="ri-list-settings-line"></i>
                                </a>
                                <a href="{{ route('admin.products.addons.index', $product) }}" class="btn {{ ($product->addons_count ?? 0) ? 'btn-success' : 'btn-secondary' }} btn-sm" title="Add-ons (suggested extra items)">
                                    <i class="ri-add-circle-line"></i>
                                    @if (($product->addons_count ?? 0))
                                        <span class="text-[10px] font-bold">{{ $product->addons_count }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                    <i class="ri-edit-box-line"></i>
                                </a>
                                <a href="{{ route('admin.products.delete', $product->id) }}"
                                   onclick="return confirm('Are you sure you want to remove this product?')"
                                   class="btn btn-danger btn-sm" title="Delete">
                                    <i class="ri-delete-bin-7-line"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <i class="ri-restaurant-2-line"></i>
                            <h3>No menu items yet</h3>
                            <p>Start by adding your first dish to the menu.</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary mt-4">
                                <i class="ri-add-line"></i> Add Menu Item
                            </a>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($products->hasPages())
        <div class="card-footer">
            {{ $products->links() }}
        </div>
    @endif
</div>

@endsection
