<?php

namespace App\Services\AI;

use App\Exceptions\AiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** Fournisseur Claude via l'API Messages d'Anthropic. */
class AnthropicProvider implements AiProvider
{
    public function name(): string
    {
        return 'anthropic';
    }

    public function generate(AiRequest $request): string
    {
        $data = $this->post([
            'system' => $request->system,
            'messages' => [['role' => 'user', 'content' => $request->prompt]],
        ]);

        return $this->texte($data);
    }

    public function chat(string $system, array $messages, array $tools, callable $runTool): string
    {
        $messages = array_map(fn (array $m) => ['role' => $m['role'], 'content' => $m['content']], $messages);

        for ($tour = 0; $tour < config('ai.chat.max_tool_rounds'); $tour++) {
            $data = $this->post(['system' => $system, 'messages' => $messages, 'tools' => $tools]);

            if (($data['stop_reason'] ?? null) !== 'tool_use') {
                return $this->texte($data);
            }

            $messages[] = ['role' => 'assistant', 'content' => $data['content']];
            $resultats = [];
            foreach ($data['content'] as $bloc) {
                if (($bloc['type'] ?? null) === 'tool_use') {
                    // Seuls les outils prédéfinis (lecture seule) sont exécutables : jamais de SQL libre.
                    $resultats[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $bloc['id'],
                        'content' => json_encode($runTool($bloc['name'], (array) ($bloc['input'] ?? [])), JSON_UNESCAPED_UNICODE),
                    ];
                }
            }
            $messages[] = ['role' => 'user', 'content' => $resultats];
        }

        throw AiException::failed('trop d’appels d’outils consécutifs.');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function post(array $payload): array
    {
        $cfg = config('ai.anthropic');

        if (empty($cfg['key'])) {
            throw AiException::failed('clé API absente.');
        }

        try {
            $response = $this->http($cfg)->post('/v1/messages', $payload + [
                'model' => $cfg['model'],
                'max_tokens' => $cfg['max_tokens'],
            ]);
        } catch (ConnectionException $e) {
            throw AiException::failed('délai dépassé ou réseau injoignable.');
        }

        if ($response->failed()) {
            throw AiException::failed('réponse HTTP '.$response->status().'.');
        }

        $data = $response->json();
        if (! is_array($data) || ! is_array($data['content'] ?? null)) {
            throw AiException::failed('réponse malformée.');
        }

        return $data;
    }

    /** @param  array<string, mixed>  $cfg */
    private function http(array $cfg): PendingRequest
    {
        return Http::baseUrl($cfg['base_url'])
            ->withHeaders(['x-api-key' => $cfg['key'], 'anthropic-version' => $cfg['version']])
            ->timeout($cfg['timeout'])
            ->acceptJson();
    }

    /** @param  array<string, mixed>  $data */
    private function texte(array $data): string
    {
        $texte = collect($data['content'] ?? [])->where('type', 'text')->pluck('text')->implode("\n");

        if ($texte === '') {
            throw AiException::failed('réponse vide.');
        }

        return $texte;
    }
}
