<?php

namespace App\Http\Requests\Institution;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PeriodRequest extends FormRequest
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
            'yearId' => 'required|exists:master_years,id',
            'institutionId' => 'required|exists:institutions,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'start' => 'required|string',
            'end' => 'required|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'yearId' => 'ID Tahun Pelajaran',
            'institutionId' => 'ID Lembaga',
            'name' => 'Nama Period',
            'description' => 'Diskripsi',
            'start' => 'Tanggal Mulai',
            'end' => 'Tanggal Selesai',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'statusMessage' => $validator->errors()->first(),
        ], 422));
    }
}
