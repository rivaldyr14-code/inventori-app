<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active'  => ['boolean'],
            'notes'      => ['nullable', 'string'],
            'attachment' => ['nullable', 'mimes:pdf', 'min:102', 'max:512'],
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
