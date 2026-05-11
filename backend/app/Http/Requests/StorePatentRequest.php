<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Patent::class);
    }

    public function rules(): array
    {
        return [
            'project_id'    => 'nullable|exists:projects,id',
            'title'         => 'required|string|max:255',
            'inventors'     => 'required|string',
            'filing_date'   => 'required|date',
            'patent_number' => 'nullable|string|max:100',
            'status_id'     => 'nullable|exists:patent_statuses,id',
        ];
    }
}