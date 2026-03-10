<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAnnouncementRequest extends FormRequest
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
            'institutionId' => 'nullable|integer|exists:institutions,id',
            'title' => 'required|string|between:1,255',
            'description' => 'required|string',
            'type' => 'required|int',
            'user_id' => 'required_if:type,specific|nullable|integer|exists:users,id',
            'is_wa_sent' => 'boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'yearId' => 'ID Tahun Ajaran',
            'institutionId' => 'ID Lembaga',
            'title' => 'Judul Pengumuman',
            'description' => 'Konten Pengumuman',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'is_wa_sent' => filter_var($this->is_wa_sent, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
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
