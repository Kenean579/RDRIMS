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
            'location' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'max_participants' => 'nullable|integer|min:1',
            'status_id' => 'sometimes|exists:event_statuses,id',
            'organizer_id' => 'sometimes|exists:users,id',
            'registration_deadline' => 'nullable|date|before_or_equal:start_date',
            'banner_id' => 'nullable|exists:files,id',
        ];
    }
}
