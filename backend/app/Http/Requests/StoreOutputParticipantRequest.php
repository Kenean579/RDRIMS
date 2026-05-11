<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutputParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $output = $this->route('output');
        return $this->user()->can('update', $output);
    }

    public function rules(): array
    {
        return [
            'user_id'             => 'required|exists:users,id',
            'participant_type_id' => 'required|exists:participant_types,id',
        ];
    }
}