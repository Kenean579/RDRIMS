<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes',
            'location' => 'sometimes',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
        ];
    }
}
