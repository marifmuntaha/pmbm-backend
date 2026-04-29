<?php

namespace App\Http\Requests\Account;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransactionRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'yearId' => 'required|integer',
            'institutionId' => 'required|integer',
            'accountId' => 'required|integer',
            'paymentId' => 'nullable|integer',
            'name' => 'required|string',
            'credit' => 'nullable|integer',
            'debit' => 'nullable|integer',
            'balance' => 'nullable|integer'
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'createdBy' => $this->user('sanctum')->id,
            'updatedBy' => $this->user('sanctum')->id,
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
