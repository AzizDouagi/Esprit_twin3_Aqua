<?php

namespace App\Services\AI;

/**
 * Requête IA : le prompt/system servent au fournisseur réel, `context` (données structurées)
 * au fournisseur local déterministe.
 */
final class AiRequest
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public readonly string $feature,
        public readonly string $system,
        public readonly string $prompt,
        public readonly array $context = [],
        public readonly bool $json = false,
    ) {}

    public function cacheKey(): string
    {
        return 'ai:'.$this->feature.':'.sha1($this->system.'|'.$this->prompt.'|'.json_encode($this->context));
    }
}
