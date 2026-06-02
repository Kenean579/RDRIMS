<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string',
            'keywords' => 'nullable|string',
            'journal_name' => 'required|string|max:255',
            'doi' => 'nullable|string|max:255',
            'url' => 'nullable|url',
            'publication_date' => 'nullable|date',
            'volume' => 'nullable|string|max:50',
            'issue' => 'nullable|string|max:50',
            'pages' => 'nullable|string|max:50',
            'access_type_id' => 'nullable|exists:publication_access_types,id',
            'status_id' => 'nullable|exists:publication_statuses,id',
            'cover_image_id' => 'nullable|exists:files,id',
        ];
    }
}
