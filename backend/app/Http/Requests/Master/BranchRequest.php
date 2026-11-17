<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $branchId = $this->route('branch') ? $this->route('branch')->id : null;

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('branches', 'code')->ignore($branchId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'is_central_warehouse' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode cabang wajib diisi.',
            'code.unique' => 'Kode cabang sudah terdaftar, gunakan kode lain.',
            'name.required' => 'Nama cabang wajib diisi.',
        ];
    }
}
