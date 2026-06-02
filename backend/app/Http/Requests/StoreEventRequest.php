<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_participants' => 'nullable|integer|min:1',
            'status_id' => 'nullable|exists:event_statuses,id',
            'organizer_id' => 'nullable|exists:users,id',
            'registration_deadline' => 'nullable|date|before_or_equal:start_date',
            'banner_id' => 'nullable|exists:files,id',
        ];
    }
}
