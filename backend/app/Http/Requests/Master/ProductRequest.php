<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'has_variants' => ['boolean'],
            'base_unit_id' => ['required', 'exists:units,id'],
            'units' => ['nullable', 'array'],
            'units.*.unit_id' => ['required', 'exists:units,id'],
            'units.*.conversion_factor' => ['required', 'numeric', 'min:1'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['required', 'string', 'max:50'],
            'variants.*.barcode' => ['nullable', 'string', 'max:50'],
            'variants.*.variant_name' => ['required', 'string', 'max:255'],
            'variants.*.attributes' => ['nullable', 'array'],
            'variants.*.purchase_price' => ['required', 'numeric', 'min:0'],
            'variants.*.initial_stock' => ['nullable', 'numeric', 'min:0'],
            'variants.*.prices' => ['required', 'array', 'min:1'],
            'variants.*.prices.*.unit_id' => ['required', 'exists:units,id'],
            'variants.*.prices.*.price_type' => ['required', 'in:retail,wholesale'],
            'variants.*.prices.*.min_quantity' => ['required', 'integer', 'min:1'],
            'variants.*.prices.*.selling_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'base_unit_id.required' => 'Satuan dasar (Base Unit) produk wajib dipilih.',
            'variants.required' => 'Minimal 1 varian produk harus didaftarkan.',
            'variants.*.sku.required' => 'Kode SKU varian wajib diisi.',
            'variants.*.variant_name.required' => 'Nama varian wajib diisi.',
            'variants.*.purchase_price.required' => 'Harga modal (HPP) varian wajib diisi.',
            'variants.*.prices.required' => 'Minimal 1 harga jual harus ditentukan.',
        ];
    }
}
