<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    public function run()
    {
        $admin = User::admin()->first();

        BusinessSetting::create([
            'user_id' => $admin->id,
            'name' => 'KFC',
            'email' => 'kfc@gmail.com',
            'phone' => '01712345678',
            'image' => 'images/kfc.png',
            'signature' => 'images/signature.png',
        ]);
    }
}
