<?php
// app/Http/Requests/EthicsRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EthicsRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('researcher') || $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'submitted_to_irb' => 'boolean',
            'comments'         => 'nullable|string',
            // optionally allow file upload for ethics document
            'ethics_document'  => 'nullable|file|mimes:pdf|max:5120',
        ];
    }
}
