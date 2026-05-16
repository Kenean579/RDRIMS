<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:academic,industry,government,ngo',
            'country' => 'required|string|max:100',
            'contact_person' => 'nullable|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
        ];
    }
}
