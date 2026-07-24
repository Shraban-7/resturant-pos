<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;

class OfflineSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return is_seller();
    }

    public function rules(): array
    {
        return [
            'orders' => 'required|array|min:1|max:25',
            'orders.*.client_order_id' => 'required|uuid',
            'orders.*.device_id' => 'required|uuid',
            'orders.*.source_order_id' => 'nullable|string|max:100',
            'orders.*.channel' => 'required|string|in:retail,dine_in,takeaway',
            'orders.*.dining_table_id' => 'nullable|integer|exists:dining_tables,id',
            'orders.*.customer_id' => 'nullable|integer|exists:customers,id',
            'orders.*.customer_name' => 'nullable|string|max:255',
            'orders.*.customer_phone' => 'nullable|string|max:50',
            'orders.*.seller_employee_id' => 'nullable|integer|exists:seller_employees,id',
            'orders.*.items' => 'required|array|min:1',
            'orders.*.items.*.product_id' => 'required|integer|exists:products,id',
            'orders.*.items.*.quantity' => 'required|numeric|min:0.01',
            'orders.*.items.*.unit_price_snapshot' => 'required|numeric|min:0',
            'orders.*.items.*.discount' => 'nullable|numeric|min:0',
            'orders.*.items.*.modifiers' => 'nullable|array',
            'orders.*.items.*.modifiers.*.id' => 'required_with:orders.*.items.*.modifiers|integer|exists:modifiers,id',
            'orders.*.items.*.modifiers.*.name' => 'nullable|string|max:255',
            'orders.*.items.*.modifiers.*.group_name' => 'nullable|string|max:100',
            'orders.*.items.*.modifiers.*.price' => 'nullable|numeric|min:0',
            'orders.*.items.*.notes' => 'nullable|string|max:255',
            'orders.*.amounts' => 'required|array',
            'orders.*.amounts.subtotal' => 'required|numeric|min:0',
            'orders.*.amounts.discount' => 'nullable|numeric|min:0',
            'orders.*.amounts.payable' => 'required|numeric|min:0',
            'orders.*.amounts.paid' => 'required|numeric|min:0',
            'orders.*.amounts.due' => 'required|numeric',
            'orders.*.amounts.payment_type' => 'required|string|in:cash,card,mobile_banking',
            'orders.*.note' => 'nullable|string|max:1000',
            'orders.*.created_at_client' => 'required|date',
            'orders.*.schema_version' => 'required|integer|in:1',
        ];
    }
}
