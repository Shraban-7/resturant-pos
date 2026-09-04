<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SaleSeeder extends Seeder
{
    public function run()
    {
        $admins = User::admin()->get();
        $products = Product::all();

        for ($i = 0; $i < 10; $i++) {
            $admin = $admins->random();
            $customer = Customer::where('admin_id', $admin->id)->inRandomOrder()->first()
                ?? Customer::create([
                    'admin_id' => $admin->id,
                    'name' => 'Walk-in Customer',
                    'phone' => '0180000000' . rand(10, 99),
                    'address' => 'Dhaka, Bangladesh',
                ]);
            $orderId = generateOrderId();

            $subtotal = 0;
            $saleItems = [];


            foreach ($products->random(rand(2, 5)) as $product) {
                $quantity = rand(1, 5);
                $unitPrice = $product->selling_price;
                $discount = rand(0, 15);
                $totalPrice = ($unitPrice * $quantity) - $discount;

                $subtotal += $totalPrice;

                $currentStock = $product->availableStock;
                $newStock = $currentStock - $quantity;

                $product->stock_out += $quantity;
                $product->save();

                ProductStock::create([
                    'product_id' => $product->id,
                    'admin_id' => $admin->id,
                    'type' => 'decrement',
                    'quantity' => $quantity,
                    'old_stock' => $currentStock,
                    'new_stock' => $newStock,
                    'buying_price' => $product->buying_price,
                    'selling_price' => $unitPrice,
                ]);

                $saleItems[] = [
                    'item_id'     => $product->id,
                    'item_name'   => $product->name,
                    'unit_price'  => $unitPrice,
                    'buying_price'  => $product->buying_price,
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

            $sale = Sale::create([
                'admin_id' => $admin->id,
                'customer_id' => $customer->id,
                'order_id' => $orderId,
                'sale_date' => now()->subDays(rand(0, 30)),
                'subtotal' => $subtotal,
                'discount' => $saleDiscount,
                'payable' => $payable,
                'paid' => $paid,
                'due' => $due,

                'note' => 'Seeded sale for ' . $customer->name,
                'payment_option' => 'cash',
            ]);

            foreach ($saleItems as $item) {
                $item['admin_id'] = $admin->id;
                $item['sale_id'] = $sale->id;
                SaleItem::create($item);
            }
        }
    }
}



