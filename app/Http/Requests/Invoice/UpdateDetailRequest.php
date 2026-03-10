<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('sanctum')->role == 3;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'invoiceId' => 'required|int|exists:invoices,id',
            'productId' => 'required|int|exists:master_products,id',
            'name' => 'required|string',
            'price' => 'required|int',
            'discount' => 'required|int',
            'amount' => 'required|int',
        ];
    }

    public function attributes(): array
    {
        return [
            'invoiceId' => 'ID Tagihan',
            'productId' => 'ID Item',
            'name' => 'Nama Item',
            'price' => 'Harga',
            'discount' => 'Potongan',
            'amount' => 'Jumlah',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'createdBy' => $this->user('sanctum')->id,
            'updatedBy' => $this->user('sanctum')->id,
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'statusMessage' => $validator->errors()->first(),
        ], 422));
    }
}
