<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $license = $this->route('license');
        $patent = $license->patent;
        return $this->user()->can('update', $patent);
    }

    public function rules(): array
    {
        return [
            'company_name' => 'sometimes|string|max:255',
            'start_date'   => 'sometimes|date',
            'end_date'     => 'sometimes|date|after_or_equal:start_date',
            'royalty_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }
}