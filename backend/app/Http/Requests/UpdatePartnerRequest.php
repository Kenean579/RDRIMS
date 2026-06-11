<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'sector' => 'sometimes|required|string|max:100',
            'contact_email' => 'sometimes|required|email|max:255',
            'website' => 'nullable|string|max:255',
            'research_center_id' => 'nullable|exists:research_centers,id',
        ];
    }
}
