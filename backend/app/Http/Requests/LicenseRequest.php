<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patent_id' => 'required|exists:patents,id',
            'licensee_name' => 'required|string',
            'license_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:license_date',
            'terms' => 'nullable|string',
        ];
    }
}
