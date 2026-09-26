<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreatesPosData;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase, CreatesPosData;

    public function test_settings_page_renders_all_tabs(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Business Settings')
            ->assertSee('Profile Settings')
            ->assertSee('POS Settings')
            ->assertSee('Receipt Settings');
    }

    public function test_business_settings_can_be_saved(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.settings.business'), [
            'tab' => 'business',
            'name' => 'My Restaurant',
            'email' => 'restaurant@example.com',
            'phone' => '01700000000',
            'address' => 'Dhaka, Bangladesh',
            'vat_number' => 'VAT-123',
            'bank_name' => 'DBBL',
            'account_holder' => 'John Doe',
            'account_number' => '123456789',
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'business']));

        $this->assertDatabaseHas('business_settings', [
            'user_id' => $admin->ownerId(),
            'name' => 'My Restaurant',
            'email' => 'restaurant@example.com',
            'phone' => '01700000000',
            'bank_name' => 'DBBL',
        ]);
    }

    public function test_pos_settings_can_be_saved(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.settings.pos'), [
            'tab' => 'pos',
            'vat_enabled' => '1',
            'vat_rate' => '15',
            'vat_mode' => 'exclusive',
            'global_discount_enabled' => '1',
            'global_discount_type' => 'percentage',
            'global_discount_rate' => '5',
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'pos']));

        $this->assertDatabaseHas('business_settings', [
            'user_id' => $admin->ownerId(),
            'vat_enabled' => true,
            'vat_rate' => 15,
            'vat_mode' => 'exclusive',
            'global_discount_enabled' => true,
            'global_discount_type' => 'percentage',
            'global_discount_rate' => 5,
        ]);
    }

    public function test_receipt_settings_can_be_saved(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.settings.receipt'), [
            'tab' => 'receipt',
            'receipt_footer' => 'See you again!',
            'receipt_show_signature' => '1',
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'receipt']));

        $this->assertDatabaseHas('business_settings', [
            'user_id' => $admin->ownerId(),
            'receipt_footer' => 'See you again!',
            'receipt_show_signature' => true,
        ]);
    }

    public function test_profile_settings_can_be_saved(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.settings.profile'), [
            'tab' => 'profile',
            'name' => 'New Admin Name',
            'email' => 'new@example.com',
            'phone' => '01800000000',
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'profile']));

        $admin->refresh();
        $this->assertSame('New Admin Name', $admin->name);
        $this->assertSame('new@example.com', $admin->email);
        $this->assertSame('01800000000', $admin->phone);
    }

    public function test_profile_password_can_be_changed(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.settings.profile'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'current_password' => 'password',
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'profile']));

        $admin->refresh();
        $this->assertTrue(Hash::check('newsecret123', $admin->password));
        $this->assertFalse(Hash::check('password', $admin->password));
    }

    public function test_profile_password_rejects_wrong_current_password(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.settings.profile'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'current_password' => 'wrong-password',
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertSessionHasErrors('current_password');

        $admin->refresh();
        $this->assertTrue(Hash::check('password', $admin->password));
    }

    public function test_validation_failure_keeps_user_on_active_tab(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.settings.receipt'), [
            'tab' => 'receipt',
            'receipt_footer' => str_repeat('x', 300), // exceeds max:255
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'receipt']))
            ->assertSessionHasErrors('receipt_footer');
    }

    public function test_legacy_settings_url_redirects_to_index(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.settings.legacy.business'))
            ->assertRedirect(route('admin.settings.index', ['tab' => 'business']));
    }

    public function test_existing_business_settings_are_updated_not_duplicated(): void
    {
        $admin = $this->createAdmin();
        BusinessSetting::create(['user_id' => $admin->id, 'name' => 'Old Name', 'email' => 'old@example.com']);

        $this->actingAs($admin)->post(route('admin.settings.business'), [
            'tab' => 'business',
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '01711111111',
            'address' => 'Chittagong',
        ]);

        $this->assertSame(1, BusinessSetting::count());
        $this->assertDatabaseHas('business_settings', [
            'user_id' => $admin->ownerId(),
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }
}