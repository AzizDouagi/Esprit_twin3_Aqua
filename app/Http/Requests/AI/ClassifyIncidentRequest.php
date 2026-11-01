<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class ClassifyIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('ai.use') ?? false) && ($this->user()?->can('incidents.report') ?? false);
    }

    public function rules(): array
    {
        return ['titre' => ['required', 'string', 'max:180'], 'description' => ['required', 'string', 'min:5', 'max:2500']];
    }
}
