<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Campus;

class StoreCampusRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Campus::class); }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:50|unique:campuses,code',
            'university_id' => 'required|exists:universities,id',
        ];
    }
}
