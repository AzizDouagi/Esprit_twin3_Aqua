<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class RiskScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ai.use') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
