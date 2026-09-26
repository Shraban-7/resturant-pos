<?php

namespace App\Actions;

use App\Enums\ProductType;
use App\Models\Product;

class ResolveProductAddonsAction
{
    /**
     * Rebuild multiselected suggested add-ons from the server catalog.
     * Client prices are never trusted; totals come from the DB.
     * Only add-ons attached to this product, owned by this panel,
     * active and still sellable are honored.
     *
     * @param  array<int, mixed>  $requested  e.g. [['id' => 1], [1], ...]
     * @return array{0: array<int, array{id: int, name: string, price: float}>, 1: float}
     */
    public function execute(Product $product, array $requested = []): array
    {
        if (empty($requested)) {
            return [[], 0.0];
        }

        $requestedIds = collect($requested)
            ->map(fn ($item) => is_array($item) ? ($item['id'] ?? null) : $item)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === (int) $product->id)
            ->unique()
            ->values();

        if ($requestedIds->isEmpty()) {
            return [[], 0.0];
        }

        $addons = $product->addons()
            ->where('products.admin_id', $product->admin_id)
            ->where('products.is_active', true)
            ->whereIn('products.type', [ProductType::DISH, ProductType::BUFFET])
            ->whereIn('products.id', $requestedIds)
            ->get(['products.id', 'products.name', 'products.selling_price']);

        $resolved = $addons
            ->map(fn ($addon) => [
                'id' => (int) $addon->id,
                'name' => $addon->name,
                'price' => (float) $addon->selling_price,
            ])
            ->values()
            ->all();

        return [$resolved, (float) collect($resolved)->sum('price')];
    }
}
