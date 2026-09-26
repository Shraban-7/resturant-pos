<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    private const TABS = ['business', 'profile', 'pos', 'receipt'];

    public function index(Request $request)
    {
        $business = $this->business();
        $user = auth()->user();

        $activeTab = $request->query('tab', 'business');
        if (! in_array($activeTab, self::TABS, true)) {
            $activeTab = 'business';
        }

        return view('admin.settings.index', compact('business', 'user', 'activeTab'));
    }

    public function updateBusiness(Request $request)
    {
        $data = $this->validateTab($request, [
            'image' => 'nullable|image|mimes:png,jpg',
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'vat_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'account_holder' => 'nullable|string',
            'account_number' => 'nullable|string',
        ]);

        $business = $this->business();

        if ($request->hasFile('image')) {
            if ($business?->image) {
                delete_file($business->image);
            }

            $data['image'] = upload_file($request->file('image'), 'business');
        }

        $this->saveBusiness($data);

        return $this->redirectToTab('business', 'Business settings saved.');
    }

    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $this->validateTab($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->filled('current_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return redirect()
                    ->route('admin.settings.index', ['tab' => 'profile'])
                    ->withErrors(['current_password' => __('Current password is incorrect.')])
                    ->withInput();
            }
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return $this->redirectToTab('profile', 'Profile settings saved.');
    }

    public function updatePos(Request $request)
    {
        $data = $this->validateTab($request, [
            'vat_enabled' => 'nullable|boolean',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'vat_mode' => 'nullable|string|in:exclusive,inclusive',
            'global_discount_enabled' => 'nullable|boolean',
            'global_discount_type' => 'nullable|string|in:flat,percentage',
            'global_discount_rate' => 'nullable|numeric|min:0',
        ]);

        $data['vat_enabled'] = $request->boolean('vat_enabled');
        $data['vat_rate'] = $data['vat_rate'] ?? 0;
        $data['vat_mode'] = $data['vat_mode'] ?? 'exclusive';
        $data['global_discount_enabled'] = $request->boolean('global_discount_enabled');
        $data['global_discount_type'] = $data['global_discount_type'] ?? 'percentage';
        $data['global_discount_rate'] = $data['global_discount_rate'] ?? 0;

        $this->saveBusiness($data);

        return $this->redirectToTab('pos', 'POS settings saved.');
    }

    public function updateReceipt(Request $request)
    {
        $data = $this->validateTab($request, [
            'receipt_header' => 'nullable|string|max:500',
            'receipt_footer' => 'nullable|string|max:255',
        ]);

        $data['receipt_header'] = trim((string) ($data['receipt_header'] ?? '')) ?: null;
        $data['receipt_footer'] = trim((string) ($data['receipt_footer'] ?? '')) ?: null;

        $this->saveBusiness($data);

        return $this->redirectToTab('receipt', 'Receipt settings saved.');
    }

    private function business(): ?BusinessSetting
    {
        return BusinessSetting::where('user_id', panel_owner_id())->first();
    }

    private function saveBusiness(array $data): void
    {
        $business = $this->business();

        if (is_null($business)) {
            BusinessSetting::create(array_merge($data, ['user_id' => panel_owner_id()]));
        } else {
            $business->update($data);
        }

        forget_store_branding();
    }

    private function validateTab(Request $request, array $rules): array
    {
        try {
            return $request->validate($rules);
        } catch (ValidationException $e) {
            $tab = $request->input('tab', 'business');
            $tab = in_array($tab, self::TABS, true) ? $tab : 'business';

            throw ValidationException::withMessages($e->errors())
                ->redirectTo(route('admin.settings.index', ['tab' => $tab]));
        }
    }

    private function redirectToTab(string $tab, string $message)
    {
        return redirect()
            ->route('admin.settings.index', ['tab' => $tab])
            ->with('success', $message);
    }
}



