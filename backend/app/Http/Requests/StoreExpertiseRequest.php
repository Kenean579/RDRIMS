<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Expertise;

class StoreExpertiseRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Expertise::class); }

    public function rules(): array
    {
        return ['name' => 'required|string|max:100|unique:expertise,name'];
    }
}
