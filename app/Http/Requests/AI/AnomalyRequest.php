<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class AnomalyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('incidents.manage') ?? false;
    }

    public function rules(): array
    {
        return $this->isMethod('post') ? ['confirmer' => ['required', 'accepted']] : [];
    }
}
