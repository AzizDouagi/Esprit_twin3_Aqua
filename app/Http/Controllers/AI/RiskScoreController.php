<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\RiskScoreRequest;
use App\Models\Infrastructure;
use App\Services\AI\AiManager;
use App\Services\AI\AiRequest;
use App\Services\RiskScoreService;
use Illuminate\View\View;

class RiskScoreController extends Controller
{
    public function __invoke(RiskScoreRequest $request, Infrastructure $infrastructure, RiskScoreService $risk, AiManager $ai): View
    {
        $score = $risk->evaluer($infrastructure);
        $result = $request->isMethod('post') ? $ai->generate(new AiRequest('risk', 'Explique brièvement ce score heuristique en français. Ne pas modifier les chiffres et ne pas prétendre prédire une panne.', json_encode($score, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), $score), $request->user()->id) : null;

        return view('ai.risk', compact('infrastructure', 'score', 'result'));
    }
}
