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
            BranchSeeder::class,
            BranchFloorSeeder::class,
            SupplierSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            SupplierProductSeeder::class,
            SupplierSaleSeeder::class,
            SaleSeeder::class,
            StaffSeeder::class,
            CatalogExtrasSeeder::class,
            IngredientSeeder::class,
        ]);
    }
}
