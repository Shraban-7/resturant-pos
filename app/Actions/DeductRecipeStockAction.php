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
     * Menu products are pure: buffet never touches inventory, dishes
     * deduct raw ingredients via recipe BOM, and dishes without a
     * recipe are always available (no finished-goods stock).
     */
    public function execute(Product $product, float $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        // Buffet is per-person with no inventory impact.
        if ($product->isBuffet()) {
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

        // Pure menu: dishes without a recipe carry no finished stock.
        return;
    }

    /**
     * Reverse a prior deduction (cart remove / qty decrease).
     */
    public function restore(Product $product, float $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        if ($product->isBuffet()) {
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

        // Nothing was deducted for recipe-less dishes.
        return;
    }

    /**
     * Whether this product uses BOM ingredient deduction.
     */
    public function usesRecipe(Product $product): bool
    {
        $recipe = $this->resolveRecipe($product);

        return $recipe && $recipe->ingredients->isNotEmpty();
    }

    /**
     * Servings still producible from raw-material availability.
     * Null = unlimited (buffet or recipe-less menu item).
     */
    public function availableServings(Product $product): ?int
    {
        if ($product->isBuffet()) {
            return null;
        }

        $recipe = $this->resolveRecipe($product);

        if (! $recipe || $recipe->ingredients->isEmpty()) {
            return null;
        }

        $servings = null;

        foreach ($recipe->ingredients as $line) {
            $ingredient = $line->ingredientProduct;
            if (! $ingredient) {
                continue;
            }

            $perServing = (float) $line->quantity;
            if ($perServing <= 0) {
                continue;
            }

            $available = (float) $ingredient->stock_in - (float) $ingredient->stock_out;
            $possible = (int) floor($available / $perServing);
            $servings = $servings === null ? $possible : min($servings, $possible);
        }

        return max(0, (int) ($servings ?? 0));
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
