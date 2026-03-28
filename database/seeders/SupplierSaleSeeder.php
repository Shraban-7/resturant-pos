<?php

namespace Database\Seeders;

use App\Models\SupplierCart;
use App\Models\SupplierCartItem;
use App\Models\SupplierProduct;
use App\Models\SupplierProductStock;
use App\Models\SupplierSale;
use App\Models\SupplierSaleItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class SupplierSaleSeeder extends Seeder
{
    public function run()
    {
        $supplier = User::supplier()->first();
        $sellers = User::seller()->get();

        $products = SupplierProduct::all();

        for ($i = 0; $i < 10; $i++) {
            $orderId = generateOrderId('SU');

            $seller = $sellers->random();

            $subtotal = 0;
            $saleItems = [];

            foreach ($products->random(rand(2, 5)) as $product) {
                $quantity = rand(5, 20);
                $unitPrice = $product->selling_price;
                $discount = rand(0, 20);
                $totalPrice = ($unitPrice * $quantity) - $discount;
                $oldStock = $product->availableStock;
                $newStock = $oldStock - $quantity;

                $product->stock_out += $quantity;
                $product->save();

                SupplierProductStock::create([
                    'product_id' => $product->id,
                    'supplier_id' => $supplier->id,
                    'type' => 'decrement',
                    'quantity' => $quantity,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'buying_price' => $product->buying_price,
                    'selling_price' => $product->selling_price,
                ]);

                $subtotal += $totalPrice;

                $saleItems[] = [
                    'item_id'     => $product->id,
                    'item_name'   => $product->name,
                    'unit_price'  => $unitPrice,
                    'buying_price' => $product->selling_price,
                    'unit'        => $product->unit->short_name,
                    'quantity'    => $quantity,
                    'total_price' => $totalPrice,
                    'note'        => 'Seeded item: ' . $product->name,
                ];
            }


            $saleDiscount = rand(10, 100);
            $payable = $subtotal - $saleDiscount;
            $paid = rand(0, $payable);
            $due = $payable - $paid;

            $sale = SupplierSale::create([
                'supplier_id' => $supplier->id,
                'customer_id' => $seller->id,
                'order_id' => $orderId,
                'sale_date' => now()->subDays(rand(0, 30)),
                'subtotal' => $subtotal,
                'discount' => $saleDiscount,
                'payable' => $payable,
                'paid' => $paid,
                'due' => $due,
                'note' => 'Seeded sale to ' . $seller->name,
                'payment_option' => 'cash',
            ]);

            foreach ($saleItems as $item) {
                $item['supplier_id'] = $supplier->id;
                $item['supplier_sale_id'] = $sale->id;

                SupplierSaleItem::create($item);
            }
        }
    }
}
