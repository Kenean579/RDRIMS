<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required',
            'abstract' => 'required',
            'call_id' => 'required|exists:calls,id',
            'budget' => 'required|numeric',
            'investigators' => 'required|array|min:1',
            'investigators.*.user_id' => 'nullable|exists:users,id',
            'investigators.*.role_id' => 'required|exists:investigator_roles,id',
        ];
    }
}
