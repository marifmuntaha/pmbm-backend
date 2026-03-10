<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('sanctum')->role === 1 ||
            $this->user('sanctum')->institutionId === $this->request->get('institutionId');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'yearId' => 'nullable|int|exists:master_years,id',
            'institutionId' => 'nullable|int|exists:institutions,id',
            'productId' => 'nullable|int|exists:products,id',
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|string',
            'unit' => 'required|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'yearId' => 'ID Tahun Ajaran',
            'institutionId' => 'ID Lembaga',
            'productId' => 'ID Item Pembayaran',
            'name' => 'Nama Potongan',
            'description' => 'Deskripsi Potongan',
            'price' => 'Harga Potongan',
            'unit' => 'Unit',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'updatedBy' => $this->user('sanctum')->id
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'statusMessage' => $validator->errors()->first(),
        ], 422));
    }
}
