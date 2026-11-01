<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveTechnician implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_scalar($value) || ! User::role([User::ROLE_TECHNICIEN, User::ROLE_ADMIN])->whereKey($value)->where('actif', true)->exists()) {
            $fail('Le technicien sélectionné doit être actif et autorisé.');
        }
    }
}
