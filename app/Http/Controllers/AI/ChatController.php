<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\ChatRequest;
use App\Services\AI\AiManager;
use App\Services\AI\ReadOnlyTools;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(AiManager $ai, ReadOnlyTools $tools): View
    {
        return view('ai.chat', ['stats' => $tools->run('network_stats', []), 'provider' => $ai->chatProviderName(), 'model' => $ai->chatProviderName() === 'ollama' ? config('ai.ollama.model') : null]);
    }

    public function store(ChatRequest $request, AiManager $ai, ReadOnlyTools $tools): JsonResponse
    {
        $sources = [];
        $stats = null;
        $runTool = function (string $name, array $input) use ($tools, &$sources, &$stats): array {
            $result = $tools->run($name, $input);
            if ($name === 'network_stats') {
                $stats = $result;
            }
            $source = $tools->source($name, $input);
            $sources[$name.':'.json_encode($input)] = $source;

            return $result;
        };
        $result = $ai->chat($request->user()->id, $request->validated('messages'), $tools->definitions(), $runTool);

        return response()->json($result + ['sources' => array_values($sources), 'stats' => $stats, 'model' => $result['provider'] === 'ollama' ? config('ai.ollama.model') : null]);
    }
}
