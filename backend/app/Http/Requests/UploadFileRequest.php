<?php
// app/Http/Requests/UploadFileRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    use \App\Traits\CastBooleanFields;

    public function authorize()
    {
        return true; // controller checks permissions
    }

    protected function prepareForValidation(): void
    {
        $this->castBooleans(['is_public']);
    }

    public function rules()
    {
        return [
            'file'        => 'required|file|max:20480', // 20MB
            'parent_type' => 'nullable|string|in:proposal,project,output,patent,agreement',
            'parent_id'   => 'nullable|integer',
            'is_public'   => 'nullable|boolean',
        ];
    }
}
