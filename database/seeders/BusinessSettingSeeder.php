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
            'name' => 'E-Restora',
            'email' => 'hello@erestora.com',
            'phone' => '01700000000',
            'image' => 'assets/images/logo.png',
            'signature' => 'images/signature.png',
        ]);

        \Illuminate\Support\Facades\Cache::forget('store.branding');
    }
}
