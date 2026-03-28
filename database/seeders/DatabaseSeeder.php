<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {

        $this->call([
            UserSeeder::class,
            BusinessSettingSeeder::class,
            ProductUnitSeeder::class,
            CategoryProductSeeder::class,
            SupplierProductSeeder::class,
            SupplierSaleSeeder::class,
            SellerSaleSeeder::class,
            SellerSeeder::class,
            // ProductSeeder::class,
        ]);
    }
}
