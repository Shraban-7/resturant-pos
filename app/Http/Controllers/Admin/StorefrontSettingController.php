<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class StorefrontSettingController extends Controller
{
    public function index()
    {
        $settings = StoreSetting::allFor(panel_owner_id());

        return view('admin.storefront-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'announcement_text' => 'nullable|string|max:500',
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:1000',
            'opening_hours' => 'nullable|string|max:255',
            'footer_note' => 'nullable|string|max:500',
        ]);

        StoreSetting::saveMany([
            'announcement_enabled' => $request->boolean('announcement_enabled') ? '1' : '0',
            'announcement_text' => $data['announcement_text'] ?? '',
            'hero_title' => $data['hero_title'] ?? '',
            'hero_subtitle' => $data['hero_subtitle'] ?? '',
            'opening_hours' => $data['opening_hours'] ?? '',
            'show_popular' => $request->boolean('show_popular') ? '1' : '0',
            'show_branches' => $request->boolean('show_branches') ? '1' : '0',
            'show_reservation' => $request->boolean('show_reservation') ? '1' : '0',
            'footer_note' => $data['footer_note'] ?? '',
        ]);

        return redirect()->route('admin.storefront-settings.index')->with('success', 'Storefront updated.');
    }
}
