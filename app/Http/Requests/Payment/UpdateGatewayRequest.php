<?php

namespace App\Http\Requests\Payment;

use HttpResponseException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGatewayRequest extends FormRequest
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
            'is_active' => 'sometimes|in:0,1,true,false',
            'mode' => 'sometimes|in:1,2,sandbox,production',
            'server_key' => 'nullable|string',
            'client_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'callback_token' => 'nullable|string',
        ];
    }

    /**
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'statusMessage' => $validator->errors()->first(),
        ], 422));
    }
}
