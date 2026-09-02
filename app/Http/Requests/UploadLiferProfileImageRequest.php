<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadLiferProfileImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->activeLifer()->exists() ?? false;
    }

    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'dimensions:max_width=4096,max_height=4096',
            ],
        ];
    }
}
