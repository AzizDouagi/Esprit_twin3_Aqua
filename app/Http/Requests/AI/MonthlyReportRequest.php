<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class MonthlyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ai.use') ?? false;
    }

    public function rules(): array
    {
        return ['mois' => ['nullable', 'date_format:Y-m'], 'format' => ['nullable', 'in:html,pdf']];
    }
}
