<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'type'       => ['required', 'string', 'in:in,out'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'is_active'  => ['boolean'],
            'notes'      => ['nullable', 'string'],
            'attachment' => ['nullable', 'mimes:pdf', 'min:102', 'max:512'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $product = Product::find($this->product_id);
            if (! $product) {
                $validator->errors()->add('product_id', 'Produk tidak ditemukan.');
                return;
            }
            if ($this->type === 'out' && $product->current_stock < $this->quantity) {
                $validator->errors()->add('quantity', "Stok tidak mencukupi. Stok saat ini: {$product->current_stock}.");
            }
        });
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk harus dipilih.',
            'type.required'       => 'Tipe transaksi harus dipilih.',
            'type.in'             => 'Tipe transaksi harus "in" atau "out".',
            'quantity.required'   => 'Jumlah harus diisi.',
            'quantity.min'        => 'Jumlah minimal adalah 1.',
            'attachment.mimes'    => 'File harus berupa PDF.',
            'attachment.min'      => 'Ukuran file minimum adalah 100 KB.',
            'attachment.max'      => 'Ukuran file maksimum adalah 500 KB.',
        ];
    }
}
