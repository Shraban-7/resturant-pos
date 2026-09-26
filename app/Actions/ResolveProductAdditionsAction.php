<?php

namespace App\Actions;

use App\Models\Product;

class ResolveProductAdditionsAction
{
    /**
     * Rebuild priced extras (additions) from the server catalog.
     * Client prices are never trusted; totals come from the DB.
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
            ->unique()
            ->values();

        if ($requestedIds->isEmpty()) {
            return [[], 0.0];
        }

        // Only extras attached to this product, owned by this panel, and active.
        $extras = $product->extras()
            ->where('additions.admin_id', $product->admin_id)
            ->where('additions.is_active', true)
            ->whereIn('additions.id', $requestedIds)
            ->get(['additions.id', 'additions.name', 'additions.price']);

        $resolved = $extras
            ->map(fn ($extra) => [
                'id' => (int) $extra->id,
                'name' => $extra->name,
                'price' => (float) $extra->price,
            ])
            ->values()
            ->all();

        $total = collect($resolved)->sum('price');

        return [$resolved, (float) $total];
    }
}
