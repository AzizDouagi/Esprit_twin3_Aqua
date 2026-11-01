<?php

namespace App\Http\Requests;

use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IncidentFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Incident::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:180'],
            'statut' => ['nullable', Rule::in(Incident::STATUTS)],
            'gravite' => ['nullable', Rule::in(Incident::GRAVITES)],
            'infrastructure_id' => ['nullable', 'integer', 'exists:infrastructures,id'],
            'technicien_id' => ['nullable', 'regex:/^(none|[1-9][0-9]*)$/'],
            'du' => ['nullable', 'date_format:Y-m-d'],
            'au' => array_merge(['nullable', 'date_format:Y-m-d'], $this->filled('du') ? ['after_or_equal:du'] : []),
            'tri' => ['nullable', Rule::in(['date_incident', 'gravite', 'statut', 'titre', 'created_at'])],
            'sens' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
