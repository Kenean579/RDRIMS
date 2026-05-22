<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'detectable_type' => 'required',
            'detectable_id' => 'required',
            'file_id' => 'required|exists:files,id',
        ];
    }
}
