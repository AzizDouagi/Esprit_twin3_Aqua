<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMesureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.access') ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'debit' => ['nullable', 'numeric', 'between:0,100000'],
            'pression' => ['nullable', 'numeric', 'between:0,100'],
            'niveau' => ['nullable', 'numeric', 'between:0,100'],
            'turbidite' => ['nullable', 'numeric', 'between:0,10000'],
            'ph' => ['nullable', 'numeric', 'between:0,14'],
            'temperature' => ['nullable', 'numeric', 'between:-20,120'],
            'mesure_le' => ['nullable', 'date', 'before_or_equal:now'],
        ];
    }
}
