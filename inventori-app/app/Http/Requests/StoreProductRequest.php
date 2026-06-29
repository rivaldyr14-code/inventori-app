<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'  => ['required', 'exists:categories,id'],
            'sku'          => ['required', 'string', 'max:100', 'unique:products,sku'],
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'price'        => ['required', 'numeric', 'min:0'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'is_active'    => ['boolean'],
            'attributes'   => ['nullable', 'json'],
            'attachment'   => ['nullable', 'mimes:pdf', 'min:102', 'max:512'],
        ];
    }

    public function messages(): array
    {
        return [
            'attachment.mimes' => 'File harus berupa PDF.',
            'attachment.min'   => 'Ukuran file minimum adalah 100 KB.',
            'attachment.max'   => 'Ukuran file maksimum adalah 500 KB.',
        ];
    }
}
