@extends('layouts.admin')
@section('title', 'Recipe — '.$product->name)
@section('page_title', 'Recipe BOM')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<a href="{{ route('admin.products.index') }}">Products</a>
<span class="separator">/</span>
<span class="current">{{ $product->name }}</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="text-lg font-semibold text-slate-800">{{ $product->name }}</h2>
        <p class="page-subtitle">Define raw ingredients deducted when this dish is sold.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Back</a>
        @if($product->recipe)
            <form action="{{ route('admin.products.recipe.destroy', $product) }}" method="post"
                  onsubmit="return confirm('Remove recipe? Stock will fall back to finished goods.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="ri-delete-bin-line"></i> Remove Recipe</button>
            </form>
        @endif
    </div>
</div>

<form action="{{ route('admin.products.recipe.update', $product) }}" method="post"
      x-data="recipeForm(@js(($product->recipe?->ingredients ?? collect())->map(fn ($i) => [
          'ingredient_product_id' => $i->ingredient_product_id,
          'quantity' => (float) $i->quantity,
      ])->values()))">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="card lg:col-span-1">
            <div class="card-body space-y-3">
                <div class="form-group">
                    <label class="form-label flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" class="rounded"
                               {{ ($product->recipe?->is_active ?? true) ? 'checked' : '' }}>
                        Recipe active
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label">Prep time (minutes)</label>
                    <input type="number" name="preparation_time_minutes" class="form-control" min="1" max="480"
                           value="{{ $product->recipe?->preparation_time_minutes ?? 15 }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Instructions</label>
                    <textarea name="instructions" class="form-control" rows="5">{{ $product->recipe?->instructions }}</textarea>
                </div>
            </div>
        </div>

        <div class="card lg:col-span-2">
            <div class="card-body">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-slate-800">Ingredients</h3>
                    <button type="button" class="btn btn-secondary btn-sm" @click="addLine()">
                        <i class="ri-add-line"></i> Add ingredient
                    </button>
                </div>

                <template x-if="lines.length === 0">
                    <p class="text-sm text-slate-500 py-6 text-center">No ingredients yet. Add raw products used by this dish.</p>
                </template>

                <div class="space-y-3">
                    <template x-for="(line, index) in lines" :key="index">
                        <div class="grid grid-cols-12 gap-2 items-end">
                            <div class="col-span-7 form-group mb-0">
                                <label class="form-label" x-show="index === 0">Ingredient</label>
                                <select class="form-select form-control" :name="`ingredients[${index}][ingredient_product_id]`" x-model="line.ingredient_product_id" required>
                                    <option value="">Select product</option>
                                    @foreach($ingredients as $ingredient)
                                        <option value="{{ $ingredient->id }}">{{ $ingredient->name }} (avail {{ $ingredient->stock_in - $ingredient->stock_out }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-3 form-group mb-0">
                                <label class="form-label" x-show="index === 0">Qty / serving</label>
                                <input type="number" step="0.001" min="0.001" class="form-control"
                                       :name="`ingredients[${index}][quantity]`" x-model="line.quantity" required>
                            </div>
                            <div class="col-span-2">
                                <button type="button" class="btn btn-danger btn-sm w-full" @click="removeLine(index)">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="card-footer flex justify-end">
                <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Save Recipe</button>
            </div>
        </div>
    </div>
</form>

@endsection

@push('footer')
<script>
function recipeForm(initial) {
    return {
        lines: initial.length ? initial : [],
        addLine() {
            this.lines.push({ ingredient_product_id: '', quantity: 1 });
        },
        removeLine(index) {
            this.lines.splice(index, 1);
        },
    };
}
</script>
@endpush

