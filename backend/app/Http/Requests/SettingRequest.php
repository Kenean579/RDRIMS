<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string|unique:settings,key,' . ($this->setting?->id ?? ''),
            'value' => 'required|string',
            'group' => 'required|string',
        ];
    }
}
