<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPhotoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'photo'       => [
                'required', 'file', 'image',
                'mimes:jpeg,jpg,png,webp,heic',
                'max:10240', // 10MB
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'category'    => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'File foto wajib dipilih.',
            'photo.image'    => 'File harus berupa gambar.',
            'photo.mimes'    => 'Format foto harus: JPEG, PNG, WebP, atau HEIC.',
            'photo.max'      => 'Ukuran foto maksimal 10MB.',
        ];
    }
}
