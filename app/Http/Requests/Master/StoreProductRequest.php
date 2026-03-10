<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user('sanctum')->role, [1, 3]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'yearId' => 'required|integer|exists:master_years,id',
            'institutionId' => 'required|integer|exists:institutions,id',
            'name' => 'required|string',
            'surname' => 'required|string',
            'price' => 'required|string',
            'gender' => 'nullable|int',
            'programId' => 'nullable|int',
            'isBoarding' => 'required|int',
            'boardingId' => 'nullable|int',
        ];
    }

    public function attributes(): array
    {
        return [
            'yearId' => 'ID Tahun Ajaran',
            'institutionId' => 'ID Lembaga',
            'name' => 'Nama Item',
            'alias' => 'Alias Item',
            'price' => 'Harga Item',
            'gender' => 'Jenis Kelamin',
            'program' => 'Program Madrasah',
            'boarding' => 'Program Boarding',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'createdBy' => $this->user('sanctum')->id,
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
