<?php
// app/Http/Requests/StoreProposalRequest.php

namespace App\Http\Requests;

use App\Models\Call;
use App\Models\ProposalType;
use Illuminate\Foundation\Http\FormRequest;

class StoreProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('researcher') || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'call_id'           => 'required|exists:calls,id',
            'type_name'         => 'required|string|exists:proposal_types,name',
            'title'             => 'required|string|max:255',
            'abstract'          => 'required|string',
            'objectives'        => 'required|string',
            'methodology'       => 'required|string',
            'keywords'          => 'required|string',
            'budget'            => 'required|numeric|min:0',
            'budget_allocation' => 'nullable|array',
            'academic_year_id'  => 'nullable|exists:academic_years,id',
            'proposal_file'     => 'nullable|file|max:20480', // 20MB
        ];
    }
}
