<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutPosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|string|exists:carts,order_id',
            'payment_type' => 'required|string|in:cash,card,mobile_banking',
            'paid_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'dining_table_id' => 'nullable|exists:dining_tables,id',
            'employee_id' => 'nullable|exists:employees,id',
            'client_order_id' => 'nullable|uuid',
            'device_id' => 'nullable|uuid',
            'created_at_client' => 'nullable|date',
        ];
    }
}



