<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Publication::class);
    }

    public function rules(): array
    {
        return [
            'project_id'       => 'nullable|exists:projects,id',
            'title'            => 'required|string|max:255',
            'abstract'         => 'nullable|string',
            'keywords'         => 'nullable|string',
            'journal'          => 'required|string|max:255',
            'doi'              => 'nullable|string|max:255',
            'scholar_url'      => 'nullable|string|max:255',
            'publication_date' => 'required|date',
            'citation_count'   => 'nullable|integer|min:0',
            'file_id'          => 'nullable|exists:files,id',
        ];
    }
}