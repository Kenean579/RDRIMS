<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => 'sometimes|string|max:500',
            'abstract'       => 'sometimes|string',
            'call_id'        => 'sometimes|nullable|exists:calls,id',
            'budget'         => 'sometimes|numeric',
            'type_id'        => 'sometimes|nullable|exists:proposal_types,id',
            'keywords'       => 'sometimes|nullable|string',
            'objectives'     => 'sometimes|nullable|string',
            'methodology'    => 'sometimes|nullable|string',
            'investigators'             => 'sometimes|array',
            'investigators.*.user_id'   => 'nullable|exists:users,id',
            'investigators.*.name'      => 'nullable|string|max:255',
            'investigators.*.email'     => 'nullable|email|max:255',
            'investigators.*.institution' => 'nullable|string|max:255',
            'investigators.*.role_id'   => 'required_with:investigators|exists:investigator_roles,id',
        ];
    }
}
