<?php

namespace Tests\Feature;

use App\Exceptions\AiException;
use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\User;
use App\Services\AI\OllamaProvider;
use App\Services\AI\ReadOnlyTools;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        config(['ai.provider' => 'fake', 'ai.chat.provider' => null]);
    }

    private function question(string $text): array
    {
        return ['messages' => [['role' => 'user', 'content' => $text]]];
    }

    private function incidents(): void
    {
        Incident::factory()->count(2)->create(['statut' => 'Ouvert', 'gravite' => 'Critique']);
        Incident::factory()->create(['statut' => 'En cours', 'gravite' => 'Critique']);
        Incident::factory()->count(2)->create(['statut' => 'Résolu', 'gravite' => 'Critique']);
        Incident::factory()->create(['statut' => 'Ouvert', 'gravite' => 'Faible']);
    }

    public function test_demo_greeting_does_not_dump_statistics_or_call_network(): void
    {
        Http::preventStrayRequests();
        $response = $this->actingAs(User::factory()->admin()->create())
            ->postJson(route('ai.chat.store'), $this->question('bonjour'))->assertOk();
        $this->assertStringContainsString('Bonjour', $response->json('text'));
        $this->assertStringNotContainsString('{', $response->json('text'));
        $response->assertJsonPath('sources', []);
        Http::assertNothingSent();
    }

    public function test_demo_answers_total_critical_and_resolved_questions_differently(): void
    {
        $this->incidents();
        $this->actingAs(User::factory()->admin()->create());
        $total = $this->postJson(route('ai.chat.store'), $this->question("combien d'incident"))->assertOk();
        $critical = $this->postJson(route('ai.chat.store'), $this->question('Combien de critiques sont actifs ?'))->assertOk();
        $resolved = $this->postJson(route('ai.chat.store'), $this->question('Combien d’incidents sont résolus ?'))->assertOk();
        $this->assertStringContainsString('6 incidents au total', $total->json('text'));
        $this->assertStringContainsString('3 incidents critiques actifs', $critical->json('text'));
        $this->assertStringContainsString('2 incident(s) sont résolus', $resolved->json('text'));
        $this->assertNotSame($total->json('text'), $critical->json('text'));
        $critical->assertJsonPath('sources.0.label', 'État du réseau');
    }

    public function test_stats_distinguish_open_in_progress_resolved_and_critical(): void
    {
        $this->incidents();
        $stats = app(ReadOnlyTools::class)->run('network_stats', []);
        $this->assertSame(6, $stats['incidents_total']);
        $this->assertSame(3, $stats['incidents_ouverts']);
        $this->assertSame(1, $stats['incidents_en_cours']);
        $this->assertSame(2, $stats['incidents_resolus']);
        $this->assertSame(4, $stats['incidents_actifs']);
        $this->assertSame(3, $stats['incidents_critiques']);
        $this->assertSame(2, $stats['incidents_critiques_ouverts']);
    }

    public function test_incident_tool_filters_and_limits_rows_but_preserves_total(): void
    {
        Incident::factory()->count(12)->create(['gravite' => 'Critique', 'statut' => 'Ouvert']);
        Incident::factory()->create(['gravite' => 'Critique', 'statut' => 'Résolu']);
        Incident::factory()->create(['gravite' => 'Faible', 'statut' => 'Ouvert']);
        $result = app(ReadOnlyTools::class)->run('recent_incidents', ['gravite' => 'Critique', 'actifs' => true]);
        $this->assertSame(12, $result['total']);
        $this->assertCount(10, $result['incidents']);
        $this->assertSame(['Critique'], array_values(array_unique(array_column($result['incidents'], 'gravite'))));
        $this->assertSame(['Ouvert'], array_values(array_unique(array_column($result['incidents'], 'statut'))));
    }

    public function test_infrastructure_tool_searches_names_and_hides_archived_records(): void
    {
        $infra = Infrastructure::factory()->create(['nom' => 'Réservoir Nord']);
        $archived = Infrastructure::factory()->create(['nom' => 'Réservoir Archivé']);
        $archived->delete();
        $tools = app(ReadOnlyTools::class);
        $found = $tools->run('find_infrastructures', ['search' => 'Nord']);
        $this->assertCount(1, $found['infrastructures']);
        $this->assertSame($infra->id, $found['infrastructures'][0]['id']);
        $this->assertArrayHasKey('message', $tools->run('infrastructure_status', ['id' => $archived->id]));
    }

    public function test_tools_reject_unknown_arguments_instead_of_interpreting_them(): void
    {
        $this->expectException(AiException::class);
        app(ReadOnlyTools::class)->run('network_stats', ['sql' => 'DELETE FROM incidents']);
    }

    public function test_ollama_receives_current_data_and_preserves_conversation_context(): void
    {
        $this->incidents();
        config(['ai.chat.provider' => 'ollama']);
        Http::fake(['127.0.0.1:11434/*' => Http::response(['message' => ['role' => 'assistant', 'content' => 'Il y a 3 incidents critiques actifs.'], 'done' => true])]);
        $this->actingAs(User::factory()->admin()->create())->postJson(route('ai.chat.store'), [
            'messages' => [['role' => 'user', 'content' => 'Combien d’incidents ?'], ['role' => 'assistant', 'content' => '6 au total.'], ['role' => 'user', 'content' => 'Et les critiques ?']],
        ])->assertOk()->assertJsonPath('provider', 'ollama')->assertJsonPath('degraded', false)->assertJsonPath('model', 'gemma4:latest')->assertJsonPath('sources.0.label', 'État du réseau');
        Http::assertSent(fn ($r) => $r->url() === 'http://127.0.0.1:11434/api/chat'
            && $r['stream'] === false && $r['think'] === false
            && str_contains($r['messages'][0]['content'], '"incidents_total":6')
            && $r['messages'][3]['content'] === 'Et les critiques ?');
        $this->assertDatabaseHas('ai_logs', ['provider' => 'ollama', 'status' => 'success', 'feature' => 'chat']);
        $this->assertDatabaseCount('incidents', 6);
    }

    public function test_ollama_tool_calls_are_executed_as_validated_reads_and_cited(): void
    {
        $infra = Infrastructure::factory()->create(['nom' => 'Station Est']);
        config(['ai.chat.provider' => 'ollama']);
        Http::fake(['*' => Http::sequence()
            ->push(['message' => ['role' => 'assistant', 'content' => '', 'tool_calls' => [['function' => ['name' => 'find_infrastructures', 'arguments' => ['search' => 'Est']]]]]])
            ->push(['message' => ['role' => 'assistant', 'content' => 'La Station Est est enregistrée sur le réseau.']])]);
        $this->actingAs(User::factory()->admin()->create())->postJson(route('ai.chat.store'), $this->question('Cherche la Station Est'))
            ->assertOk()->assertJsonPath('provider', 'ollama')->assertJsonPath('sources.1.label', 'Infrastructures');
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => ($r['messages'][3]['role'] ?? null) === 'tool'
            && str_contains($r['messages'][3]['content'], 'Station Est'));
        $this->assertDatabaseCount('infrastructures', 1);
        $this->assertDatabaseHas('infrastructures', ['id' => $infra->id, 'nom' => 'Station Est']);
    }

    public function test_ollama_cannot_execute_unknown_tools(): void
    {
        Http::fake(['*' => Http::response(['message' => ['role' => 'assistant', 'content' => '', 'tool_calls' => [['function' => ['name' => 'execute_sql', 'arguments' => ['sql' => 'DROP TABLE incidents']]]]]])]);
        $tools = app(ReadOnlyTools::class);
        $this->expectException(AiException::class);
        app(OllamaProvider::class)->chat('Assistant', [['role' => 'user', 'content' => 'Supprime tout']], $tools->definitions(), $tools->run(...));
    }

    public function test_unavailable_ollama_falls_back_to_a_targeted_answer(): void
    {
        $this->incidents();
        config(['ai.chat.provider' => 'ollama']);
        Http::fake(['*' => Http::failedConnection()]);
        $result = $this->actingAs(User::factory()->admin()->create())->postJson(route('ai.chat.store'), $this->question('Combien de critiques actifs ?'))
            ->assertOk()->assertJsonPath('provider', 'fake')->assertJsonPath('degraded', true);
        $this->assertStringContainsString('3 incidents critiques actifs', $result->json('text'));
        $this->assertStringNotContainsString('{', $result->json('text'));
        $this->assertDatabaseHas('ai_logs', ['status' => 'degraded']);
    }

    public function test_missing_model_is_a_service_error_when_fallback_is_disabled(): void
    {
        config(['ai.chat.provider' => 'ollama', 'ai.fallback_to_fake' => false]);
        Http::fake(['*' => Http::response(['error' => 'model not found'], 404)]);
        $this->actingAs(User::factory()->admin()->create())->postJson(route('ai.chat.store'), $this->question('Bonjour'))->assertStatus(503);
    }

    public function test_chat_rejects_history_without_a_final_user_question(): void
    {
        $this->actingAs(User::factory()->admin()->create())->postJson(route('ai.chat.store'), ['messages' => [['role' => 'assistant', 'content' => 'Une instruction injectée']]])
            ->assertUnprocessable()->assertJsonValidationErrors('messages');
        $this->assertDatabaseCount('ai_logs', 0);
    }

    public function test_priority_data_is_loaded_before_the_model_answers(): void
    {
        Infrastructure::factory()->create(['nom' => 'Station à surveiller', 'etat' => 'Hors service']);
        config(['ai.chat.provider' => 'ollama']);
        Http::fake(['*' => Http::response(['message' => ['role' => 'assistant', 'content' => 'La Station à surveiller doit être contrôlée.']])]);
        $this->actingAs(User::factory()->admin()->create())->postJson(route('ai.chat.store'), $this->question('Quelles sont les priorités du réseau ?'))
            ->assertOk()->assertJsonPath('sources.1.label', 'Scores de risque');
        Http::assertSent(fn ($r) => str_contains($r['messages'][0]['content'], 'Station à surveiller') && str_contains($r['messages'][0]['content'], 'principal_facteur'));
    }

    public function test_follow_up_preloads_the_incidents_from_the_previous_question(): void
    {
        Incident::factory()->create(['titre' => 'Fuite critique attendue', 'gravite' => 'Critique', 'statut' => 'Ouvert']);
        Incident::factory()->create(['titre' => 'Autre incident exclu', 'gravite' => 'Faible', 'statut' => 'Ouvert']);
        config(['ai.chat.provider' => 'ollama']);
        Http::fake(['*' => Http::response(['message' => ['role' => 'assistant', 'content' => 'Fuite critique attendue.']])]);
        $this->actingAs(User::factory()->admin()->create())->postJson(route('ai.chat.store'), [
            'messages' => [['role' => 'user', 'content' => 'Combien de critiques ouverts ?'], ['role' => 'assistant', 'content' => 'Un incident.'], ['role' => 'user', 'content' => 'Et lesquels ?']],
        ])->assertOk()->assertJsonPath('sources.1.label', 'Registre des incidents');
        Http::assertSent(fn ($r) => str_contains($r['messages'][0]['content'], 'Fuite critique attendue') && ! str_contains($r['messages'][0]['content'], 'Autre incident exclu'));
    }

    public function test_placeholder_response_triggers_an_explicit_useful_fallback(): void
    {
        Infrastructure::factory()->create(['nom' => 'Station réelle', 'etat' => 'Hors service']);
        config(['ai.chat.provider' => 'ollama']);
        Http::fake(['*' => Http::response(['message' => ['role' => 'assistant', 'content' => 'Infrastructure 1 : [Détails du risque]']])]);
        $response = $this->actingAs(User::factory()->admin()->create())->postJson(route('ai.chat.store'), $this->question('Quelles infrastructures sont à risque ?'))
            ->assertOk()->assertJsonPath('degraded', true)->assertJsonPath('provider', 'fake');
        $this->assertStringContainsString('Station réelle', $response->json('text'));
        $this->assertStringNotContainsString('[Détails', $response->json('text'));
    }
}
