<?php

namespace App\Services;

use App\Models\Product;
use InvalidArgumentException;

class StockService
{
    public function availableQuantity(Product $product): float
    {
        return (float) ($product->stock_in - $product->stock_out);
    }

    public function hasAvailableStock(Product $product, float $requestedQuantity): bool
    {
        if ($requestedQuantity <= 0) {
            return false;
        }

        return $this->availableQuantity($product) >= $requestedQuantity;
    }

    /**
     * Increase stock_out (deduct available inventory).
     *
     * @throws InvalidArgumentException when requested quantity exceeds available stock
     */
    public function deductStock(Product $product, float $quantity): Product
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Deduction quantity must be greater than zero.');
        }

        if (!$this->hasAvailableStock($product, $quantity)) {
            throw new InvalidArgumentException("Insufficient stock available for product: {$product->name}");
        }

        $product->increment('stock_out', $quantity);
        $product->refresh();

        return $product;
    }

    /**
     * Decrease stock_out (return inventory to availability).
     */
    public function restoreStock(Product $product, float $quantity): Product
    {
        if ($quantity <= 0) {
            return $product;
        }

        $restorable = min($quantity, (float) $product->stock_out);
        if ($restorable <= 0) {
            return $product;
        }

        $product->decrement('stock_out', $restorable);
        $product->refresh();

        return $product;
    }
}
