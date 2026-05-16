<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoURequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partner_id' => 'required|exists:partners,id',
            'title' => 'required|string|max:255',
            'signing_date' => 'required|date',
            'expiry_date' => 'required|date|after:signing_date',
            'file_id' => 'nullable|exists:files,id',
            'status' => 'required|in:active,expired,terminated',
        ];
    }
}
