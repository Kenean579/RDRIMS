<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Event::class);
    }

    public function rules(): array
    {
        return [
            'title'                 => 'required|string|max:255',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'venue'                 => 'required|string|max:255',
            'description'           => 'nullable|string',
            'capacity'              => 'nullable|integer|min:1',
            'registration_deadline' => 'nullable|date|before_or_equal:start_date',
            'image_file_id'         => 'nullable|exists:files,id',
        ];
    }
}