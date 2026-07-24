<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutPosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return is_seller();
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
            'seller_employee_id' => 'nullable|exists:seller_employees,id',
        ];
    }
}
