<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class SummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('ai.use') ?? false) && ($this->user()?->can('view', $this->route('incident')) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
