<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;

class PosAddItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return is_seller();
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|string|exists:carts,order_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'discount' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
        ];
    }
}
