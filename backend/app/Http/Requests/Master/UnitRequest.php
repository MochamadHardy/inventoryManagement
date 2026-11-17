<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'short_name' => ['required', 'string', 'max:15'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama satuan unit wajib diisi.',
            'short_name.required' => 'Singkatan satuan wajib diisi (misal: pcs, lsn).',
        ];
    }
}
