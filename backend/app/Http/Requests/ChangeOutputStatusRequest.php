<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeOutputStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $output = $this->route('output');
        return $this->user()->can('changeStatus', $output);
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:submitted,approved_by_supervisor,approved,rejected',
        ];
    }
}
