<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'metadata'    => ['nullable', 'json'],
            'attachment'  => ['nullable', 'mimes:pdf', 'min:102', 'max:512'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attachment.mimes' => 'File harus berupa PDF.',
            'attachment.min'   => 'Ukuran file minimum adalah 100 KB.',
            'attachment.max'   => 'Ukuran file maksimum adalah 500 KB.',
        ];
    }
}
