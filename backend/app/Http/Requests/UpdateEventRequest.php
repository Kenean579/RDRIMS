<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');
        return $this->user()->can('update', $event);
    }

    public function rules(): array
    {
        return [
            'title'                 => 'sometimes|string|max:255',
            'start_date'            => 'sometimes|date',
            'end_date'              => 'sometimes|date|after_or_equal:start_date',
            'venue'                 => 'sometimes|string|max:255',
            'description'           => 'nullable|string',
            'capacity'              => 'nullable|integer|min:1',
            'registration_deadline' => 'nullable|date|before_or_equal:start_date',
            'image_file_id'         => 'nullable|exists:files,id',
        ];
    }
}