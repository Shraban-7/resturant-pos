<?php

namespace Database\Seeders;

use App\Models\ProductUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductUnitSeeder extends Seeder
{
    public function run()
    {
        $units = [
            ['name' => 'BOX', 'short_name' => 'box'],
            ['name' => 'PIECES', 'short_name' => 'pcs'],
            ['name' => 'BAGS', 'short_name' => 'bag'],
            ['name' => 'PACKS', 'short_name' => 'pac'],
            ['name' => 'DOZENS', 'short_name' => 'dzn'],
            ['name' => 'LITRE', 'short_name' => 'ltr'],
            ['name' => 'CANS', 'short_name' => 'can'],
            ['name' => 'BUNDLES', 'short_name' => 'bdl'],
            ['name' => 'KILOGRAMS', 'short_name' => 'kg'],
            ['name' => 'TICKET', 'short_name' => 'tkt'],
            ['name' => 'BOTTLES', 'short_name' => 'btl'],
            ['name' => 'SETS', 'short_name' => 'set'],
            ['name' => 'CASE', 'short_name' => 'case'],
            ['name' => 'INCHES', 'short_name' => 'in'],
            ['name' => 'BOR', 'short_name' => 'bor'],
            ['name' => 'POUCH', 'short_name' => 'poch'],
            ['name' => 'JARS', 'short_name' => 'jar'],
            ['name' => 'TABLETS', 'short_name' => 'tbs'],
            ['name' => 'SQUARE METERS', 'short_name' => 'sqm'],
            ['name' => 'UNITS', 'short_name' => 'unit'],
            ['name' => 'SQUARE FEET', 'short_name' => 'sqft'],
            ['name' => 'QUINTAL', 'short_name' => 'qtl'],
            ['name' => 'PAIRS', 'short_name' => 'pair'],
            ['name' => 'NUMBERS', 'short_name' => 'num'],
            ['name' => 'MILILITRE', 'short_name' => 'ml'],
            ['name' => 'METERS', 'short_name' => 'mtr'],
            ['name' => 'FEET', 'short_name' => 'ft'],
            ['name' => 'CENTIMETER', 'short_name' => 'cm'],
        ];

        foreach ($units as  $unit) {
            ProductUnit::create([
                'name' => $unit['name'],
                'short_name' => $unit['short_name']
            ]);
        }
    }
}
