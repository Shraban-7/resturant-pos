<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceQrOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.note' => 'nullable|string|max:255',
            'items.*.modifiers' => 'nullable|array',
            'items.*.modifiers.*.id' => 'required_with:items.*.modifiers|integer|exists:modifiers,id',
            'items.*.modifiers.*.name' => 'nullable|string|max:255',
            'items.*.modifiers.*.price' => 'nullable|numeric|min:0',
            'items.*.modifiers.*.group_name' => 'nullable|string|max:100',
        ];
    }
}
