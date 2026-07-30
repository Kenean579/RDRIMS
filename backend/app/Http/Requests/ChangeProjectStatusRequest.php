<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeProjectStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller via policy
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:draft,planning,active,suspended,completed,closed',
            'reason' => 'required_if:status,suspended,rejected|string|max:1000',
            'comments' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required_if' => 'A reason is required when suspending or rejecting a project.',
            'status.in' => 'Invalid status. Must be one of: draft, planning, active, suspended, completed, closed',
        ];
    }
}
