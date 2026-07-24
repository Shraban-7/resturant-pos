<?php

namespace App\Actions;

use App\Models\Product;
use RuntimeException;

class ResolveProductModifiersAction
{
    /**
     * Validate required groups and rebuild modifiers from the server catalog.
     *
     * @param  array<int, array{id?: mixed}>  $requested
     * @return array{0: array<int, array{id: int, name: string, group_name: string, price: float}>, 1: float}
     */
    public function execute(Product $product, array $requested = []): array
    {
        $modifiers = $product->relationLoaded('modifiers')
            ? $product->modifiers
            : $product->modifiers()->where('modifiers.is_active', true)->get();

        $active = $modifiers->where('is_active', true)->values();

        $requestedIds = collect($requested)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $missingGroups = $active
            ->filter(fn ($m) => (bool) ($m->pivot->is_required ?? false))
            ->groupBy(fn ($m) => $m->group_name ?: 'Options')
            ->filter(fn ($group) => $group->pluck('id')->intersect($requestedIds)->isEmpty())
            ->keys();

        if ($missingGroups->isNotEmpty()) {
            $names = $missingGroups->implode(', ');
            throw new RuntimeException("Required modifier group(s) missing: {$names}");
        }

        $resolved = $active
            ->whereIn('id', $requestedIds)
            ->map(fn ($m) => [
                'id' => (int) $m->id,
                'name' => $m->name,
                'group_name' => $m->group_name,
                'price' => (float) $m->price,
            ])
            ->values()
            ->all();

        $unitPrice = (float) $product->selling_price + collect($resolved)->sum('price');

        return [$resolved, $unitPrice];
    }
}
