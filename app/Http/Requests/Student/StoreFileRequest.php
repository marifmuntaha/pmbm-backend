<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFileRequest extends FormRequest
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
            'userId' => 'required|exists:users,id',
            'imagePhoto' => 'nullable|image:mimes:jpeg,png,jpg|max:1024',
            'imageKk' => 'required|image:mimes:jpeg,png,jpg|max:1024',
            'imageKtp' => 'nullable|image:mimes:jpeg,png,jpg|max:1024',
            'numberAkta' => 'nullable|string',
            'imageAkta' => 'required|image:mimes:jpeg,png,jpg|max:1024',
            'numberIjazah' => 'nullable|string',
            'imageIjazah' => 'nullable|image:mimes:jpeg,png,jpg|max:1024',
            'numberSkl' => 'nullable|string',
            'imageSkl' => 'nullable|image:mimes:jpeg,png,jpg|max:1024',
            'numberKip' => 'nullable|string',
            'imageKip' => 'nullable|image:mimes:jpeg,png,jpg|max:1024',
        ];
    }

    public function attributes(): array
    {
        return [
            'userId' => 'ID Pengguna',
            'imagePhoto' => 'Pass Photo',
            'imageKk' => 'Foto Kartu Keluarga',
            'imageKtp' => 'Foto KTP Ayah',
            'numberAkta' => 'Nomor Akta',
            'imageAkta' => 'Foto/Scan Akta',
            'numberIjazah' => 'Nomor Ijazah',
            'imageIjazah' => 'Foto/Scan Ijazah',
            'numberSkl' => 'Nomor SKL',
            'imageSkl' => 'Foto/Scan SKL',
            'numberKip' => 'Nomor KIP',
            'imageKip' => 'Foto KIP',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'createdBy' => $this->user()->id,
            'updatedBy' => $this->user()->id,
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
