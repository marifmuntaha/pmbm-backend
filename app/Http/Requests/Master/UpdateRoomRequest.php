<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'capacity' => 'sometimes|required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kamar harus diisi',
            'name.string' => 'Nama kamar harus berupa teks',
            'name.max' => 'Nama kamar maksimal 255 karakter',
            'capacity.required' => 'Kapasitas harus diisi',
            'capacity.integer' => 'Kapasitas harus berupa angka',
            'capacity.min' => 'Kapasitas minimal 1',
        ];
    }
}
