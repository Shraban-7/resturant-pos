<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $business = BusinessSetting::where('user_id', panel_owner_id())->first();

        if ($request->isMethod('GET')) {
            return view('admin.settings.index', compact('business'));
        }

        $data = $request->validate([
            'image' => 'nullable|image|mimes:png,jpg',

            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'vat_number' => 'nullable|string',
            'vat_enabled' => 'nullable|boolean',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'vat_mode' => 'nullable|string|in:exclusive,inclusive',
            'global_discount_enabled' => 'nullable|boolean',
            'global_discount_type' => 'nullable|string|in:flat,percentage',
            'global_discount_rate' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string',
            'account_holder' => 'nullable|string',
            'account_number' => 'nullable|string',
            'signature' => 'nullable|string',
        ]);

        $image = $business->image ?? null;
        $signature = $business->signature ?? null;

        if ($request->hasFile('image')) {
            $image = upload_file($request->file('image'), 'business');
        }

        if ($request->signature != null) {

            $signatureData = $request->input('signature');
            $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
            $signatureData = str_replace(' ', '+', $signatureData);
            $signatureImage = base64_decode($signatureData);
            $fileName = 'signature_' . time() . '.png';

            $signaturePath = $signature = "signatures/{$fileName}";

            Storage::disk('public')->put($signaturePath, $signatureImage);

            if (isset($business->signature)) {
                delete_file($business->signature);
            }
        }

        $data['signature'] = $signature;
        $data['image'] = $image;
        $data['user_id'] = panel_owner_id();
        $data['vat_enabled'] = $request->boolean('vat_enabled');
        $data['vat_rate'] = $data['vat_rate'] ?? 0;
        $data['vat_mode'] = $data['vat_mode'] ?? 'exclusive';
        $data['global_discount_enabled'] = $request->boolean('global_discount_enabled');
        $data['global_discount_type'] = $data['global_discount_type'] ?? 'percentage';
        $data['global_discount_rate'] = $data['global_discount_rate'] ?? 0;

        is_null($business) ? BusinessSetting::create($data) : $business->update($data);

        forget_store_branding();

        return redirect()->back()->with('success', 'Settings saved.');
    }
}



