<?php

namespace App\Http\Requests;

use App\Models\LiferProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLiferProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->activeLifer()->exists() ?? false;
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'array'],
            'show_money' => ['required', 'boolean'],
            'relationship_status' => ['nullable', 'string', Rule::in(array_keys(LiferProfile::RELATIONSHIP_LABELS))],
            'public_diploma_ids' => ['present', 'array'],
            'public_diploma_ids.*' => ['integer', 'distinct', 'exists:diplomas,id'],
        ];
    }
}
