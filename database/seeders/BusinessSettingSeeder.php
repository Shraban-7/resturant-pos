<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    public function run()
    {
        $seller = User::seller()->first();
        BusinessSetting::create([
            'user_id' => $seller->id,
            'name' => 'KFC',
            'email' => 'kfc@gmail.com',
            'phone' => '01712345678',
            'image' => 'images/kfc.png',
            'signature' => 'images/signature.png',
        ]);

        $supplier = User::supplier()->first();
        BusinessSetting::create([
            'user_id' => $supplier->id,
            'name' => 'ChalDal',
            'email' => 'chaldal@gmail.com',
            'phone' => '01712345678',
            'image' => 'images/chaldal.png',
            'signature' => 'images/signature.png',
        ]);
    }
}
