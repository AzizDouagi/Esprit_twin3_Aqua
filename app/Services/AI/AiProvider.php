<?php

namespace App\Services\AI;

interface AiProvider
{
    public function name(): string;

    /** Génère un texte (ou un JSON sérialisé si $request->json). */
    public function generate(AiRequest $request): string;

    /**
     * Conversation avec outils en lecture seule.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<int, array{name: string, description: string, input_schema: array<string, mixed>}>  $tools
     * @param  callable(string, array<string, mixed>): mixed  $runTool  exécute un outil prédéfini
     */
    public function chat(string $system, array $messages, array $tools, callable $runTool): string;
}
