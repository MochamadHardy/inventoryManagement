<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_branch_id' => ['required', 'exists:branches,id'],
            'to_branch_id' => ['required', 'exists:branches,id', 'different:from_branch_id'],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'auto_send' => ['nullable', 'boolean'], // Jika true, langsung ubah ke in_transit
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_branch_id.required' => 'Cabang asal pengirim wajib dipilih.',
            'to_branch_id.required' => 'Cabang tujuan penerima wajib dipilih.',
            'to_branch_id.different' => 'Cabang tujuan tidak boleh sama dengan cabang asal pengirim.',
            'transfer_date.required' => 'Tanggal transfer surat jalan wajib diisi.',
            'items.required' => 'Minimal 1 barang harus dimasukkan ke dalam surat jalan transfer.',
            'items.*.quantity.min' => 'Jumlah barang transfer minimal 1.',
        ];
    }
}
