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
    public function index(): View
    {
        return view('ai.chat');
    }

    public function store(ChatRequest $request, AiManager $ai, ReadOnlyTools $tools): JsonResponse
    {
        return response()->json($ai->chat($request->user()->id, $request->validated('messages'), $tools->definitions(), $tools->run(...)));
    }
}
