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

        // Buffet has unlimited seats — capacity is managed by tables/reservations.
        if ($product->isBuffet()) {
            return true;
        }

        return $this->availableQuantity($product) >= $requestedQuantity;
    }

    /**
     * Increase stock_out (deduct available inventory).
     * Locks the product row so concurrent sales cannot oversell.
     *
     * @throws InvalidArgumentException when requested quantity exceeds available stock
     */
    public function deductStock(Product $product, float $quantity): Product
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Deduction quantity must be greater than zero.');
        }

        // Buffet never touches inventory.
        if ($product->isBuffet()) {
            return $product;
        }

        $locked = Product::query()
            ->whereKey($product->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        if (! $this->hasAvailableStock($locked, $quantity)) {
            throw new InvalidArgumentException("Insufficient stock available for product: {$locked->name}");
        }

        $locked->increment('stock_out', $quantity);
        $locked->refresh();
        $product->setRawAttributes($locked->getAttributes(), true);

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

        $locked = Product::query()
            ->whereKey($product->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        $restorable = min($quantity, (float) $locked->stock_out);
        if ($restorable <= 0) {
            return $product;
        }

        $locked->decrement('stock_out', $restorable);
        $locked->refresh();
        $product->setRawAttributes($locked->getAttributes(), true);

        return $product;
    }
}
