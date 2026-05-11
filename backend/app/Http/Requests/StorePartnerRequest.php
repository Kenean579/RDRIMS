<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Partner::class);
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'sector'        => 'required|string|max:100',
            'contact_email' => 'required|email|max:255',
            'website'       => 'nullable|string|max:255',
        ];
    }
}