<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFundingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fundingId = $this->route('funding')->id;

        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'total_amount' => 'sometimes|required|numeric|min:0.01',
            'currency' => 'sometimes|required|string|size:3',
            'start_date' => 'sometimes|required|date|before:end_date',
            'end_date' => 'sometimes|required|date|after:start_date',
        ];
    }
}
