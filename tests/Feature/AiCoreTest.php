<?php

namespace Tests\Feature;

use App\Exceptions\AiException;
use App\Models\User;
use App\Services\AI\AiManager;
use App\Services\AI\AiRequest;
use App\Services\AI\AnthropicProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiCoreTest extends TestCase
{
    use RefreshDatabase;

    private function request(): AiRequest
    {
        return new AiRequest('classify', 'JSON uniquement', 'Fuite', ['titre' => 'Petite fuite', 'description' => 'mineure'], true);
    }

    public function test_fake_is_deterministic_cached_and_audited_without_prompts(): void
    {
        config(['ai.provider' => 'fake']);
        $user = User::factory()->create();
        $manager = app(AiManager::class);
        $a = $manager->generate($this->request(), $user->id);
        $b = $manager->generate($this->request(), $user->id);
        $this->assertSame('Fuite', json_decode($a['text'], true)['type']);
        $this->assertSame($a['text'], $b['text']);
        $this->assertTrue($b['cached']);
        $this->assertDatabaseCount('ai_logs', 2);
        $this->assertDatabaseHas('ai_logs', ['cached' => true, 'status' => 'success']);
        Http::assertNothingSent();
    }

    public function test_anthropic_request_headers_and_payload(): void
    {
        config(['ai.anthropic.key' => 'test-key']);
        Http::fake(['api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => '{"type":"Fuite"}']]])]);
        $this->assertSame('{"type":"Fuite"}', app(AnthropicProvider::class)->generate($this->request()));
        Http::assertSent(fn ($r) => $r->hasHeader('x-api-key', 'test-key') && $r['model'] === config('ai.anthropic.model') && $r['max_tokens'] === 1024 && $r->url() === 'https://api.anthropic.com/v1/messages');
    }

    public function test_upstream_error_falls_back_explicitly(): void
    {
        config(['ai.provider' => 'anthropic', 'ai.anthropic.key' => 'test-key']);
        Http::fake(['*' => Http::response([], 503)]);
        $result = app(AiManager::class)->generate($this->request(), User::factory()->create()->id);
        $this->assertTrue($result['degraded']);
        $this->assertSame('fake', $result['provider']);
        $this->assertDatabaseHas('ai_logs', ['status' => 'degraded']);
    }

    public function test_timeout_can_be_reported_without_fallback(): void
    {
        config(['ai.provider' => 'anthropic', 'ai.anthropic.key' => 'test-key', 'ai.fallback_to_fake' => false]);
        Http::fake(['*' => Http::failedConnection()]);
        $this->expectException(AiException::class);
        app(AiManager::class)->generate($this->request(), User::factory()->create()->id);
    }

    public function test_rate_limit_applies_even_to_cached_requests(): void
    {
        config(['ai.provider' => 'fake', 'ai.rate_limit' => 1]);
        $user = User::factory()->create();
        app(AiManager::class)->generate($this->request(), $user->id);
        $this->expectExceptionCode(429);
        app(AiManager::class)->generate($this->request(), $user->id);
    }
}
