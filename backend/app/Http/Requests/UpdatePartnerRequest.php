<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $partner = $this->route('partner');
        return $this->user()->can('update', $partner);
    }

    public function rules(): array
    {
        return [
            'name'          => 'sometimes|string|max:255',
            'sector'        => 'sometimes|string|max:100',
            'contact_email' => 'sometimes|email|max:255',
            'website'       => 'nullable|string|max:255',
        ];
    }
}