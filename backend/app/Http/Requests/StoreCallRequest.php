<?php
// app/Http/Requests/StoreCallRequest.php

namespace App\Http\Requests;

use App\Models\CallStatus;
use Illuminate\Foundation\Http\FormRequest;

class StoreCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin') || $this->user()->hasRole('research_admin');
    }

    public function rules(): array
    {
        return [
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'deadline'         => 'required|date|after:today',
            'thematic_areas'   => 'required|string',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'status_name'      => 'nullable|string|exists:call_statuses,name', // dynamic
            'guideline_file'   => 'nullable|file|max:10240',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Default status to 'draft' if not provided
        if (!$this->has('status_name')) {
            $this->merge(['status_name' => 'draft']);
        }
    }
}
