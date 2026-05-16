<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can request detection
    }

    public function rules(): array
    {
        return [
            'detectable_type' => 'required|string|in:App\\Models\\Proposal,App\\Models\\Output',
            'detectable_id' => 'required|integer',
            'file_id' => 'required|exists:files,id',
            'service_id' => 'required|exists:detection_services,id',
        ];
    }
}
