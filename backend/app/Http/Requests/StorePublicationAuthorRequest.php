<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicationAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $publication = $this->route('publication');
        return $this->user()->can('update', $publication);
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'external_author_name' => 'nullable|string|max:255',
            'external_institution' => 'nullable|string|max:255',
            'author_order' => 'required|integer|min:1',
            'contribution_role' => 'nullable|in:first_author,corresponding_author,co_author',
            'is_corresponding' => 'nullable|boolean',
        ];
    }
}