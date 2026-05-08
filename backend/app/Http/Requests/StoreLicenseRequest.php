<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $patent = $this->route('patent');
        return $this->user()->can('update', $patent);
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'royalty_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }
}