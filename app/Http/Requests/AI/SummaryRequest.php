<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class SummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('ai.use') && $user->can('view', $this->route('incident'));
    }

    public function rules(): array
    {
        return [];
    }
}
