<?php

namespace Tests\Feature;

use App\Models\DemandeRetrait;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetraitsAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $promoteur;

    private User $marchand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->promoteur = User::factory()->create(['role' => 'promoteur']);
        $this->promoteur->portefeuille->update(['solde_disponible' => 10000, 'solde_en_attente' => 0]);
        $this->marchand = User::factory()->create(['role' => 'marchand']);
    }

    private function demander(float $montant = 3000, array $surcharges = []): array
    {
        return array_merge([
            'montant' => $montant,
            'moyen_paiement' => 'T-Money',
            'numero_compte' => '90011223',
        ], $surcharges);
    }

    public function test_demande_retrait_baisse_le_solde_disponible(): void
    {
        $this->actingAs($this->promoteur)
            ->post('/promoteur/portefeuille/retrait', $this->demander())
            ->assertRedirect(route('promoteur.portefeuille'));

        $demande = DemandeRetrait::sole();
        $this->assertSame('en_attente', $demande->statut);
        $this->assertEquals(3000, (float) $demande->montant);

        $portefeuille = $this->promoteur->portefeuille->fresh();
        $this->assertEquals(7000, (float) $portefeuille->solde_disponible);

        $transaction = Transaction::sole();
        $this->assertSame('retrait', $transaction->type);
        $this->assertSame('en_attente', $transaction->statut);
        $this->assertEquals(3000, (float) $transaction->montant); // positif en base
        $this->assertSame($demande->id, $transaction->demande_retrait_id);
    }

    public function test_montant_sous_le_minimum_est_refuse(): void
    {
        $this->actingAs($this->promoteur)
            ->post('/promoteur/portefeuille/retrait', $this->demander(1000))
            ->assertSessionHasErrors('montant');

        $this->assertSame(0, DemandeRetrait::count());
        $this->assertEquals(10000, (float) $this->promoteur->portefeuille->fresh()->solde_disponible);
    }

    public function test_montant_superieur_au_solde_est_refuse(): void
    {
        $this->actingAs($this->promoteur)
            ->post('/promoteur/portefeuille/retrait', $this->demander(15000))
            ->assertSessionHasErrors('montant');

        $this->assertSame(0, DemandeRetrait::count());
    }

    public function test_une_seule_demande_en_attente_a_la_fois(): void
    {
        $this->actingAs($this->promoteur)->post('/promoteur/portefeuille/retrait', $this->demander());

        $this->actingAs($this->promoteur)
            ->post('/promoteur/portefeuille/retrait', $this->demander(2000))
            ->assertSessionHasErrors('montant');

        $this->assertSame(1, DemandeRetrait::count());
        $this->assertEquals(7000, (float) $this->promoteur->portefeuille->fresh()->solde_disponible);
    }

    public function test_validation_par_ladmin(): void
    {
        $this->actingAs($this->promoteur)->post('/promoteur/portefeuille/retrait', $this->demander());
        $demande = DemandeRetrait::sole();

        $this->actingAs($this->admin)
            ->post("/admin/retraits/{$demande->id}/valider")
            ->assertRedirect(route('admin.retraits'));

        $demande->refresh();
        $this->assertSame('validee', $demande->statut);
        $this->assertSame($this->admin->id, $demande->validee_par);

        $transaction = Transaction::sole();
        $this->assertSame('payee', $transaction->statut);
        // Le montant reste prélevé : le virement est fait hors application
        $this->assertEquals(7000, (float) $this->promoteur->portefeuille->fresh()->solde_disponible);
    }

    public function test_refus_recredite_le_montant(): void
    {
        $this->actingAs($this->promoteur)->post('/promoteur/portefeuille/retrait', $this->demander());
        $demande = DemandeRetrait::sole();

        $this->actingAs($this->admin)
            ->post("/admin/retraits/{$demande->id}/refuser")
            ->assertRedirect(route('admin.retraits'));

        $this->assertSame('refusee', $demande->fresh()->statut);
        $this->assertSame(0, Transaction::count());
        $this->assertEquals(10000, (float) $this->promoteur->portefeuille->fresh()->solde_disponible);
    }

    public function test_les_actions_sont_idempotentes(): void
    {
        $this->actingAs($this->promoteur)->post('/promoteur/portefeuille/retrait', $this->demander());
        $demande = DemandeRetrait::sole();

        $this->actingAs($this->admin)->post("/admin/retraits/{$demande->id}/valider");
        $this->actingAs($this->admin)->post("/admin/retraits/{$demande->id}/valider");
        $this->actingAs($this->admin)->post("/admin/retraits/{$demande->id}/refuser");

        $demande->refresh();
        $this->assertSame('validee', $demande->statut); // la validation gagne, le refus ne change plus rien
        $this->assertSame('payee', Transaction::sole()->statut);
    }

    public function test_refus_puis_validation_nagit_pas(): void
    {
        $this->actingAs($this->promoteur)->post('/promoteur/portefeuille/retrait', $this->demander());
        $demande = DemandeRetrait::sole();

        $this->actingAs($this->admin)->post("/admin/retraits/{$demande->id}/refuser");
        $this->actingAs($this->admin)->post("/admin/retraits/{$demande->id}/valider");

        $this->assertSame('refusee', $demande->fresh()->statut);
        $this->assertEquals(10000, (float) $this->promoteur->portefeuille->fresh()->solde_disponible);
    }

    public function test_un_promoteur_ou_marchand_ne_peut_pas_valider(): void
    {
        $this->actingAs($this->promoteur)->post('/promoteur/portefeuille/retrait', $this->demander());
        $demande = DemandeRetrait::sole();

        $this->actingAs($this->promoteur)->get('/admin/retraits')->assertForbidden();
        $this->actingAs($this->promoteur)->post("/admin/retraits/{$demande->id}/valider")->assertForbidden();
        $this->actingAs($this->marchand)->post("/admin/retraits/{$demande->id}/refuser")->assertForbidden();

        $this->assertSame('en_attente', $demande->fresh()->statut);
    }

    public function test_page_admin_affiche_les_demandes_masquees(): void
    {
        $this->actingAs($this->promoteur)->post('/promoteur/portefeuille/retrait', $this->demander());

        $this->actingAs($this->admin)
            ->get('/admin/retraits')
            ->assertOk()
            ->assertSee('1 demande en attente')
            ->assertSee($this->promoteur->name)
            ->assertSee('T-Money')
            ->assertSee('•••• 1223')
            ->assertDontSee('90011223');
    }

    public function test_page_utilisateurs_avec_recherche(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/utilisateurs?q='.$this->promoteur->email)
            ->assertOk()
            ->assertSee($this->promoteur->name)
            ->assertDontSee($this->marchand->name);
    }

    public function test_moyen_paiement_invalide_refuse(): void
    {
        $this->actingAs($this->promoteur)
            ->post('/promoteur/portefeuille/retrait', $this->demander(3000, ['moyen_paiement' => 'Espèces']))
            ->assertSessionHasErrors('moyen_paiement');
    }
}
