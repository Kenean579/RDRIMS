<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Expertise;

class UpdateExpertiseRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update', $this->route('expertise')); }

    public function rules(): array
    {
        return ['name' => 'required|string|max:100|unique:expertise,name,' . $this->route('expertise')->id];
    }
}
