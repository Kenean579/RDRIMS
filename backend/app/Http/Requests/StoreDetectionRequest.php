<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDetectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if user has permission to create detection requests
        if (!auth()->user()->hasAnyRole(['researcher', 'faculty', 'admin', 'super_admin', 'detection_officer'])) {
            return false;
        }

        // File existence will be checked by validation rules
        // File ownership will be checked after validation
        return true;
    }

    public function rules(): array
    {
        return [
            'detectable_type' => [
                'required',
                'string',
                'max:50',
                Rule::in(['Proposal', 'Output', 'Publication']),
            ],
            'detectable_id' => [
                'required',
                'integer',
                'min:1',
            ],
            'file_id' => [
                'required',
                'integer',
                'exists:files,id',
                function ($attribute, $value, $fail) {
                    // Check file ownership after existence is validated
                    $file = \App\Models\File::find($value);
                    if ($file && !auth()->user()->can('view', $file)) {
                        $fail('You do not have permission to use this file.');
                    }
                },
            ],
            'service_id' => [
                'sometimes',
                'integer',
                'exists:detection_services,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'detectable_type.in' => 'The detectable type must be one of: Proposal, Output, Publication',
            'file_id.exists' => 'The selected file does not exist',
            'service_id.exists' => 'The selected service does not exist',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        // Ensure service_id defaults to 1 if not provided
        if (!$this->has('service_id')) {
            $this->merge(['service_id' => 1]);
        }
    }
}
