<?php

namespace App\Http\Requests\Institution;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActivityRequest extends FormRequest
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
            'yearId' => ['required', 'integer', 'exists:master_years,id'],
            'institutionId' => ['required', 'integer', 'exists:institutions,id'],
            'capacity' => ['required', 'integer'],
            'file' => ['required', 'mimes:pdf', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'yearId' => 'ID Tahun',
            'institutionId' => ' ID Lembaga',
            'capacity' => 'Daya Tampung',
            'file' => 'File Brosur',
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
