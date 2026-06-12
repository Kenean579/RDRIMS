<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFileRequest extends FormRequest
{
    use \App\Traits\CastBooleanFields;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->castBooleans(['is_public']);
    }

    public function rules(): array
    {
        return [
            'is_public'   => 'sometimes|boolean',
            'description' => 'nullable|string'
        ];
    }
}
