<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreInstitutionRequest extends FormRequest
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
            'name' => 'required|string',
            'surname' => 'required|string',
            'tagline' => 'required|string',
            'npsn' => 'required|string',
            'nsm' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string',
            'website' => 'required|string',
            'head' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg|max:1024',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Lembaga',
            'surname' => 'Singkatan',
            'tagline' => 'Tagline',
            'npsn' => 'NPSN',
            'nsm' => 'NSM',
            'address' => 'Alamat Lembaga',
            'phone' => 'Nomor Telepon',
            'email' => 'Alamat Email',
            'website' => 'Website',
            'head' => 'Kepala Madrasah',
            'image' => 'Logo Madrasah',
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
