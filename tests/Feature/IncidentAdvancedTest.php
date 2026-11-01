<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\User;
use App\Notifications\IncidentAssigneNotification;
use App\Notifications\IncidentCritiqueNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IncidentAdvancedTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_transitions_are_enforced_and_audited(): void
    {
        $tech = User::factory()->technicien()->create();
        $incident = Incident::factory()->create(['statut' => 'Ouvert', 'resolu_le' => null]);

        $this->actingAs($tech)->patch(route('incidents.statut', $incident), ['statut' => 'Résolu'])
            ->assertSessionHasErrors('statut');
        $this->assertSame('Ouvert', $incident->fresh()->statut);

        $this->actingAs($tech)->patch(route('incidents.statut', $incident), ['statut' => 'En cours'])->assertSessionHasNoErrors();
        $this->actingAs($tech)->patch(route('incidents.statut', $incident), ['statut' => 'Résolu'])->assertSessionHasNoErrors();

        $incident->refresh();
        $this->assertSame('Résolu', $incident->statut);
        $this->assertNotNull($incident->resolu_le);

        $this->assertDatabaseHas('incident_historiques', [
            'incident_id' => $incident->id, 'user_id' => $tech->id, 'champ' => 'Statut',
            'ancienne_valeur' => 'En cours', 'nouvelle_valeur' => 'Résolu',
        ]);
    }

    public function test_assignment_notifies_technician_and_is_audited(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $tech = User::factory()->technicien()->create();
        $incident = Incident::factory()->create(['gravite' => 'Faible', 'technicien_id' => null]);

        $this->actingAs($admin)->patch(route('incidents.assigner', $incident), ['technicien_id' => $tech->id])->assertSessionHasNoErrors();

        Notification::assertSentTo($tech, IncidentAssigneNotification::class);
        $this->assertDatabaseHas('incident_historiques', ['incident_id' => $incident->id, 'champ' => 'Technicien', 'nouvelle_valeur' => $tech->name]);
    }

    public function test_critical_incident_notifies_staff_by_mail_and_database(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $tech = User::factory()->technicien()->create();
        $simple = User::factory()->simpleUser()->create();

        Incident::factory()->create(['gravite' => 'Critique']);

        Notification::assertSentTo([$admin, $tech], IncidentCritiqueNotification::class, fn ($n, $channels) => $channels === ['mail', 'database']);
        Notification::assertNotSentTo($simple, IncidentCritiqueNotification::class);
    }

    public function test_notification_bell_and_mark_as_read(): void
    {
        $admin = User::factory()->admin()->create();
        Incident::factory()->create(['gravite' => 'Critique']);

        $this->assertSame(1, $admin->unreadNotifications()->count());
        $this->actingAs($admin)->get('/admin')->assertSee('bi-bell', false);

        $notification = $admin->unreadNotifications()->first();
        $this->actingAs($admin)->post(route('notifications.read', $notification->id))->assertRedirect();
        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }

    public function test_comments_and_attachments(): void
    {
        Storage::fake('local');
        $tech = User::factory()->technicien()->create();
        $incident = Incident::factory()->create();

        $this->actingAs($tech)->post(route('incidents.commentaires.store', $incident), ['contenu' => 'Équipe sur place.'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('incident_commentaires', ['incident_id' => $incident->id, 'contenu' => 'Équipe sur place.']);

        $this->actingAs($tech)->post(route('incidents.pieces-jointes.store', $incident), [
            'fichiers' => [UploadedFile::fake()->image('fuite.jpg')],
        ])->assertSessionHasNoErrors();
        $pj = $incident->piecesJointes()->firstOrFail();
        Storage::disk('local')->assertExists($pj->chemin);

        $this->actingAs($tech)->get(route('incidents.pieces-jointes.show', [$incident, $pj]))->assertOk();
        $this->actingAs($tech)->post(route('incidents.pieces-jointes.store', $incident), [
            'fichiers' => [UploadedFile::fake()->create('virus.exe', 10)],
        ])->assertSessionHasErrors('fichiers.0');

        $this->actingAs($tech)->get(route('incidents.show', $incident))->assertOk()->assertSee('Équipe sur place.')->assertSee('fuite.jpg');
    }

    public function test_user_declares_incident_and_only_sees_own(): void
    {
        Storage::fake('local');
        Notification::fake();
        $user = User::factory()->simpleUser()->create();
        $other = User::factory()->simpleUser()->create();
        $infra = Infrastructure::factory()->create();
        $foreign = Incident::factory()->create(['declarant_id' => $other->id]);

        $this->actingAs($user)->get(route('espace.declarer'))->assertOk();
        $this->actingAs($user)->post(route('espace.declarer.store'), [
            'titre' => 'Fuite devant chez moi', 'description' => 'Une fuite importante sur la chaussée.',
            'type' => 'Fuite', 'gravite' => 'Moyenne', 'infrastructure_id' => $infra->id,
            'photos' => [UploadedFile::fake()->image('photo.jpg')],
        ])->assertSessionHasNoErrors();

        $incident = Incident::where('titre', 'Fuite devant chez moi')->firstOrFail();
        $this->assertSame($user->id, $incident->declarant_id);
        $this->assertSame('Ouvert', $incident->statut);
        $this->assertCount(1, $incident->piecesJointes);

        $this->actingAs($user)->get(route('espace.signalements'))->assertOk()->assertSee('Fuite devant chez moi')->assertDontSee($foreign->titre);
        $this->actingAs($user)->get(route('espace.signalements.show', $incident))->assertOk();
        $this->actingAs($user)->get(route('espace.signalements.show', $foreign))->assertForbidden();
        $this->actingAs($user)->post(route('incidents.commentaires.store', $foreign), ['contenu' => 'intrusion'])->assertForbidden();
    }

    public function test_declaration_validation(): void
    {
        $user = User::factory()->simpleUser()->create();

        $this->actingAs($user)->from(route('espace.declarer'))->post(route('espace.declarer.store'), ['titre' => ''])
            ->assertSessionHasErrors(['titre', 'description', 'type', 'gravite', 'infrastructure_id']);
    }

    public function test_advanced_filters_and_sorting(): void
    {
        $admin = User::factory()->admin()->create();
        $tech = User::factory()->technicien()->create();
        $infra = Infrastructure::factory()->create();
        Incident::factory()->create(['titre' => 'Cible', 'gravite' => 'Critique', 'statut' => 'Ouvert', 'infrastructure_id' => $infra->id, 'technicien_id' => $tech->id, 'date_incident' => '2026-06-15']);
        Incident::factory()->create(['titre' => 'Autre', 'gravite' => 'Faible', 'statut' => 'Résolu', 'date_incident' => '2026-01-10']);

        $this->actingAs($admin)->get(route('incidents.index', [
            'gravite' => 'Critique', 'statut' => 'Ouvert', 'infrastructure_id' => $infra->id, 'technicien_id' => $tech->id, 'du' => '2026-06-01', 'au' => '2026-06-30',
        ]))->assertOk()->assertSee('Cible')->assertDontSee('Autre');

        $this->actingAs($admin)->get(route('incidents.index', ['tri' => 'gravite', 'sens' => 'desc']))->assertOk()->assertSeeInOrder(['Cible', 'Autre']);
    }

    public function test_incident_exports(): void
    {
        $admin = User::factory()->admin()->create();
        Incident::factory()->create(['titre' => 'Export CSV incident']);

        $csv = $this->actingAs($admin)->get(route('incidents.export-csv'));
        $csv->assertOk();
        $this->assertStringContainsString('Export CSV incident', $csv->streamedContent());
        $this->actingAs($admin)->get(route('incidents.export-pdf'))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_only_admin_can_delete_incident(): void
    {
        $tech = User::factory()->technicien()->create();
        $incident = Incident::factory()->create();

        $this->actingAs($tech)->delete(route('incidents.destroy', $incident))->assertForbidden();
        $this->assertModelExists($incident);
    }
}
