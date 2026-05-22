<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMoURequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partner_id' => 'sometimes|exists:partners,id',
            'title' => 'sometimes',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
        ];
    }
}
