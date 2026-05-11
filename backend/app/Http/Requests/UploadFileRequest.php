<?php
// app/Http/Requests/UploadFileRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    public function authorize()
    {
        return true; // controller checks permissions
    }

    public function rules()
    {
        return [
            'file'        => 'required|file|max:20480', // 20MB
            'parent_type' => 'required|string|in:proposal,project,output,patent,agreement',
            'parent_id'   => 'required|integer',
            'is_public'   => 'boolean',
        ];
    }
}
