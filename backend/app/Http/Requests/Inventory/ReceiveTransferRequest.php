<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['required_with:items', 'exists:stock_transfer_items,id'],
            'items.*.quantity_received' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}
