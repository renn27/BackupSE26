<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadBackupRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'backup' => [
                'required', 'file',
                'max:51200', // 50MB
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'category'    => ['nullable', 'string', 'max:100'],
        ];
    }
}
