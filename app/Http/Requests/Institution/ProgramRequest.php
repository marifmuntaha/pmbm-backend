<?php

namespace App\Http\Requests\Institution;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProgramRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user('sanctum')->role, [1, 2]);
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
            'name' => 'required|string|max:255',
            'alias' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'boarding' => 'nullable|string|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'yearId' => 'ID Tahun Pelajaran',
            'institutionId' => 'ID Lembaga',
            'name' => 'Nama Program',
            'alias' => 'Singkatan Program',
            'description' => 'Deskripsi Program',
            'boarding' => 'Program Boarding',

        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'statusMessage' => $validator->errors(),
        ], 422));
    }
}
