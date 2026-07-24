<?php

namespace App\Services;

use App\Models\Product;
use InvalidArgumentException;

class StockService
{
    public function hasAvailableStock(Product $product, float $requestedQuantity): bool
    {
        return ($product->stock_in - $product->stock_out) >= $requestedQuantity;
    }

    public function deductStock(Product $product, float $quantity): Product
    {
        if (!$this->hasAvailableStock($product, $quantity)) {
            throw new InvalidArgumentException("Insufficient stock available for product: {$product->name}");
        }

        $product->increment('stock_out', $quantity);
        return $product;
    }

    public function restoreStock(Product $product, float $quantity): Product
    {
        $product->decrement('stock_out', max(0, $quantity));
        return $product;
    }
}
