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
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'capacity' => 'nullable|integer|min:1',
            'registration_deadline' => 'nullable|date|before_or_equal:start_date',
            'image_file_id' => 'nullable|exists:files,id',
            'research_center_id' => 'nullable|exists:research_centers,id',
        ];
    }
}