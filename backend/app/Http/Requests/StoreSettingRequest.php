<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Setting;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Setting::class); }

    public function rules(): array
    {
        return [
            'key'         => 'required|string|max:255|unique:settings,key',
            'value'       => 'required|string',
            'description' => 'nullable|string',
        ];
    }
}
