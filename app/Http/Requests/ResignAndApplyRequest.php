<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResignAndApplyRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Changez ceci selon votre logique d'autorisation
    }

    public function rules()
    {
        return [
            'newJobId' => 'required|exists:jobs,id', // Assurez-vous que newJobId correspond à un ID de job valide
        ];
    }
}
