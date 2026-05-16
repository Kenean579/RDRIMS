<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:500',
            'abstract' => 'nullable|string',
            'journal_name' => 'required|string',
            'doi' => 'nullable|string|unique:publications,doi,' . ($this->publication?->id ?? ''),
            'publication_date' => 'required|date',
            'url' => 'nullable|url',
            'publication_type_id' => 'required|exists:publication_types,id',
            'file_id' => 'nullable|exists:files,id',
        ];
    }
}
