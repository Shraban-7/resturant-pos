<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\Recipe;
use App\Services\StockService;

class DeductRecipeStockAction
{
    public function __construct(private StockService $stockService)
    {
    }

    /**
     * Deduct inventory for a sold quantity.
     * Uses recipe BOM ingredients when present; otherwise deducts the sellable product.
     */
    public function execute(Product $product, float $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        $recipe = $this->resolveRecipe($product);

        if ($recipe && $recipe->ingredients->isNotEmpty()) {
            foreach ($recipe->ingredients as $line) {
                $ingredient = $line->ingredientProduct;
                if (!$ingredient) {
                    continue;
                }

                $deductQty = (float) $line->quantity * $quantity;
                $this->stockService->deductStock($ingredient, $deductQty);
            }

            return;
        }

        $this->stockService->deductStock($product, $quantity);
    }

    /**
     * Reverse a prior deduction (cart remove / qty decrease).
     */
    public function restore(Product $product, float $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        $recipe = $this->resolveRecipe($product);

        if ($recipe && $recipe->ingredients->isNotEmpty()) {
            foreach ($recipe->ingredients as $line) {
                $ingredient = $line->ingredientProduct;
                if (!$ingredient) {
                    continue;
                }

                $restoreQty = (float) $line->quantity * $quantity;
                $this->stockService->restoreStock($ingredient, $restoreQty);
            }

            return;
        }

        $this->stockService->restoreStock($product, $quantity);
    }

    /**
     * Whether this product uses BOM ingredient deduction.
     */
    public function usesRecipe(Product $product): bool
    {
        $recipe = $this->resolveRecipe($product);

        return $recipe && $recipe->ingredients->isNotEmpty();
    }

    private function resolveRecipe(Product $product): ?Recipe
    {
        if ($product->relationLoaded('recipe')) {
            $recipe = $product->recipe;
        } else {
            $recipe = Recipe::query()
                ->where('product_id', $product->id)
                ->with(['ingredients.ingredientProduct'])
                ->first();
        }

        if ($recipe && !$recipe->relationLoaded('ingredients')) {
            $recipe->load(['ingredients.ingredientProduct']);
        }

        if ($recipe && isset($recipe->is_active) && !$recipe->is_active) {
            return null;
        }

        return $recipe;
    }
}
