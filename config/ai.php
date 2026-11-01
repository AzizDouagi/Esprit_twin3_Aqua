<?php

return [
    /*
    | Fournisseur IA : "anthropic" (Claude), "ollama" (modèle local) ou "fake" (heuristiques locales déterministes, hors ligne).
    | Sans clé API, le fournisseur "fake" est utilisé automatiquement.
    */
    'provider' => env('AI_PROVIDER', 'fake'),

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('AI_MODEL', 'claude-sonnet-5-5'),
        'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
        'version' => '2023-06-01',
        'max_tokens' => (int) env('AI_MAX_TOKENS', 1024),
        'timeout' => (int) env('AI_TIMEOUT', 20),
    ],

    // Durée de cache (secondes) des réponses identiques, 0 pour désactiver.
    'cache_ttl' => (int) env('AI_CACHE_TTL', 600),

    // Requêtes IA autorisées par utilisateur et par minute.
    'rate_limit' => (int) env('AI_RATE_LIMIT', 20),

    // En cas d'échec du fournisseur réel, bascule sur le fournisseur local (réponse marquée "dégradée").
    'fallback_to_fake' => (bool) env('AI_FALLBACK', true),

    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
        'model' => env('OLLAMA_MODEL', 'gemma4:latest'),
        'timeout' => (int) env('OLLAMA_TIMEOUT', 60),
    ],
    'chat' => [
        'provider' => env('AI_CHAT_PROVIDER'),
        'max_tool_rounds' => 4,
    ],
];
