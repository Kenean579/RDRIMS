<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Setting;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update', $this->route('setting')); }

    public function rules(): array
    {
        return [
            'key'         => 'sometimes|string|max:255|unique:settings,key,' . $this->route('setting')->id,
            'value'       => 'required|string',
            'description' => 'nullable|string',
        ];
    }
}
