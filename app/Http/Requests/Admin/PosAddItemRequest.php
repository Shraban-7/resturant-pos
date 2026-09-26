<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PosAddItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|string|exists:carts,order_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'discount' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'modifiers' => 'nullable|array',
            'modifiers.*.id' => 'required_with:modifiers|integer|exists:modifiers,id',
            'modifiers.*.name' => 'nullable|string|max:255',
            'modifiers.*.price' => 'nullable|numeric|min:0',
            'modifiers.*.group_name' => 'nullable|string|max:100',
            'additions' => 'nullable|array',
            'additions.*.id' => 'required_with:additions|integer|exists:additions,id',
            'addons' => 'nullable|array',
            'addons.*.id' => 'required_with:addons|integer|exists:products,id',
        ];
    }
}


