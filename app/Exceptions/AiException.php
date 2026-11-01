<?php

namespace App\Exceptions;

use RuntimeException;

class AiException extends RuntimeException
{
    public static function rateLimited(int $secondes): self
    {
        return new self("Limite d’appels IA atteinte. Réessayez dans {$secondes} s.", 429);
    }

    public static function failed(string $detail): self
    {
        return new self("Le service IA est indisponible : {$detail}", 503);
    }
}
