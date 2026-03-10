<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateInvoiceRequest extends FormRequest
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
            'yearId' => 'required|integer|exists:master_years,id',
            'institutionId' => 'required|integer|exists:institutions,id',
            'userId' => 'required|integer|exists:users,id',
            'reference' => 'nullable|string',
            'name' => 'required|string',
            'amount' => 'required|string',
            'dueDate' => 'required|date:format:Y-m-d',
            'status' => 'required|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'yearId' => 'ID Tahun',
            'institutionId' => 'ID Lembaga',
            'userId' => 'ID Pengguna',
            'reference' => 'Nomor Tagihan',
            'name' => 'Diskripsi Tagihan',
            'amount' => 'Total Tagihan',
            'dueDate' => 'Jatuh Tempo',
            'status' => 'Status',
        ];
    }

    public function prepareForValidation()
    {
        return $this->merge([
            'createdBy' => $this->user('sanctum')->id,
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
