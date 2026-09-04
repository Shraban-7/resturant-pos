<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::admin()->orderBy('id')->first()->id;

        $suppliers = [
            ['name' => 'Karwan Bazar Traders', 'phone' => '01766666666', 'address' => 'Karwan Bazar, Dhaka'],
            ['name' => 'New Market Supply Co.', 'phone' => '01777777777', 'address' => 'New Market, Dhaka'],
            ['name' => 'Fresh Daily Agro', 'phone' => '01788888888', 'address' => 'Uttara, Dhaka'],
        ];

        foreach ($suppliers as $data) {
            $supplier = Supplier::firstOrCreate(
                ['admin_id' => $ownerId, 'name' => $data['name']],
                $data + ['admin_id' => $ownerId, 'is_active' => true]
            );

            // One demo purchase per supplier against a random raw ingredient.
            $ingredient = Product::where('admin_id', $ownerId)
                ->rawIngredients()
                ->inRandomOrder()
                ->first();

            if ($ingredient && ! Purchase::where('admin_id', $ownerId)->where('supplier_id', $supplier->id)->exists()) {
                $qty = rand(20, 60);
                $oldStock = $ingredient->stock_in - $ingredient->stock_out;

                Purchase::create([
                    'admin_id' => $ownerId,
                    'supplier_id' => $supplier->id,
                    'product_id' => $ingredient->id,
                    'quantity' => $qty,
                    'buying_price' => $ingredient->buying_price,
                    'total_price' => $qty * $ingredient->buying_price,
                    'purchase_date' => now()->subDays(rand(0, 10))->toDateString(),
                    'note' => 'Opening stock purchase',
                ]);

                $ingredient->increment('stock_in', $qty);

                ProductStock::create([
                    'product_id' => $ingredient->id,
                    'admin_id' => $ownerId,
                    'type' => 'increment',
                    'quantity' => $qty,
                    'old_stock' => $oldStock,
                    'new_stock' => $oldStock + $qty,
                    'buying_price' => $ingredient->buying_price,
                    'selling_price' => $ingredient->selling_price,
                ]);
            }
        }
    }
}
