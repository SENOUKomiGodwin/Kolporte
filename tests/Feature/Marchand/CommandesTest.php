<?php

namespace Tests\Feature\Marchand;

use App\Models\Article;
use App\Models\Boutique;
use App\Models\Commande;
use App\Models\LienPromoteur;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandesTest extends TestCase
{
    use RefreshDatabase;

    private User $marchand;

    private User $autreMarchand;

    private Boutique $boutique;

    private Article $article;

    private User $promoteur;

    private LienPromoteur $lien;

    protected function setUp(): void
    {
        parent::setUp();

        $this->marchand = User::factory()->create(['role' => 'marchand']);
        $this->boutique = Boutique::create(['marchand_id' => $this->marchand->id, 'nom' => 'Chez Akossiwa']);
        $this->article = Article::create([
            'boutique_id' => $this->boutique->id,
            'titre' => 'Pagne wax',
            'prix' => 12000,
            'stock' => 10,
            'type_commission' => 'pourcentage',
            'valeur_commission' => 10,
        ]);

        $this->autreMarchand = User::factory()->create(['role' => 'marchand']);
        Boutique::create(['marchand_id' => $this->autreMarchand->id, 'nom' => 'Autre boutique']);

        $this->promoteur = User::factory()->create(['role' => 'promoteur']);
        $this->lien = LienPromoteur::create([
            'promoteur_id' => $this->promoteur->id,
            'article_id' => $this->article->id,
            'code' => LienPromoteur::genererCode(),
        ]);
    }

    /** Commande payée de 2 articles via le lien (commission 2 400 en attente). */
    private function commandePayee(int $quantite = 2): Commande
    {
        $this->withCookie('ref', $this->lien->code)
            ->post("/p/{$this->article->id}/commander", [
                'nom' => 'Achat Test',
                'telephone' => '+229 90 11 22 33',
                'adresse_livraison' => 'Cotonou, Haie Vive',
                'quantite' => $quantite,
            ]);
        $commande = Commande::sole();
        $this->post("/commande/{$commande->id}/paiement");

        return $commande->fresh();
    }

    public function test_livraison_confirme_commande_et_deplace_la_commission(): void
    {
        $commande = $this->commandePayee();
        $this->assertSame('payee', $commande->statut);
        $this->assertEquals(2400, (float) $this->promoteur->portefeuille->fresh()->solde_en_attente);

        $this->actingAs($this->marchand)
            ->post("/marchand/commandes/{$commande->id}/livraison")
            ->assertRedirect(route('marchand.commandes.show', $commande));

        $this->assertSame('livree', $commande->fresh()->statut);

        $transaction = Transaction::sole();
        $this->assertSame('validee', $transaction->statut);

        $portefeuille = $this->promoteur->portefeuille->fresh();
        $this->assertEquals(0, (float) $portefeuille->solde_en_attente);
        $this->assertEquals(2400, (float) $portefeuille->solde_disponible);
    }

    public function test_double_confirmation_ne_credit_pas_deux_fois(): void
    {
        $commande = $this->commandePayee();

        $this->actingAs($this->marchand)->post("/marchand/commandes/{$commande->id}/livraison");
        $this->actingAs($this->marchand)->post("/marchand/commandes/{$commande->id}/livraison");

        $portefeuille = $this->promoteur->portefeuille->fresh();
        $this->assertEquals(2400, (float) $portefeuille->solde_disponible);
        $this->assertEquals(0, (float) $portefeuille->solde_en_attente);
        $this->assertSame(1, Transaction::where('statut', 'validee')->count());
    }

    public function test_annulation_remet_le_stock_et_supprime_la_commission(): void
    {
        $commande = $this->commandePayee();
        $this->assertSame(8, (int) $this->article->fresh()->stock);

        $this->actingAs($this->marchand)
            ->post("/marchand/commandes/{$commande->id}/annulation")
            ->assertRedirect();

        $this->assertSame('annulee', $commande->fresh()->statut);
        $this->assertSame(10, (int) $this->article->fresh()->stock);
        $this->assertSame(0, Transaction::count());
        $this->assertEquals(0, (float) $this->promoteur->portefeuille->fresh()->solde_en_attente);
    }

    public function test_annulation_dune_commande_livree_est_ignoree(): void
    {
        $commande = $this->commandePayee();
        $this->actingAs($this->marchand)->post("/marchand/commandes/{$commande->id}/livraison");

        $this->actingAs($this->marchand)->post("/marchand/commandes/{$commande->id}/annulation");

        $this->assertSame('livree', $commande->fresh()->statut);
        $this->assertSame(8, (int) $this->article->fresh()->stock); // pas de remise en stock
        $this->assertSame(1, Transaction::count()); // commission validée conservée
    }

    public function test_un_marchand_ne_peut_pas_agir_sur_la_commande_dun_autre(): void
    {
        $commande = $this->commandePayee();

        $this->actingAs($this->autreMarchand)->get("/marchand/commandes/{$commande->id}")->assertForbidden();
        $this->actingAs($this->autreMarchand)->post("/marchand/commandes/{$commande->id}/livraison")->assertForbidden();
        $this->actingAs($this->autreMarchand)->post("/marchand/commandes/{$commande->id}/annulation")->assertForbidden();
        $this->actingAs($this->promoteur)->post("/marchand/commandes/{$commande->id}/livraison")->assertForbidden();

        $this->assertSame('payee', $commande->fresh()->statut);
        $this->assertEquals(2400, (float) $this->promoteur->portefeuille->fresh()->solde_en_attente);
    }

    public function test_page_commandes_filtre_par_statut(): void
    {
        $this->commandePayee();
        $this->post("/p/{$this->article->id}/commander", [
            'nom' => 'AcheteurEnAttente', 'telephone' => '1', 'adresse_livraison' => 'x', 'quantite' => 1,
        ]);

        $this->actingAs($this->marchand)
            ->get('/marchand/commandes?statut=payee')
            ->assertOk()
            ->assertSee('Achat Test')
            ->assertDontSee('AcheteurEnAttente');

        $this->actingAs($this->marchand)->get('/marchand/commandes')->assertOk()->assertSee('En attente de paiement');
    }

    public function test_portefeuille_promoteur_affiche_ventes_et_historique(): void
    {
        $commande = $this->commandePayee();
        $this->actingAs($this->marchand)->post("/marchand/commandes/{$commande->id}/livraison");

        $this->actingAs($this->promoteur)
            ->get('/promoteur/portefeuille')
            ->assertOk()
            ->assertSee('Solde disponible')
            ->assertSee('2 400 FCFA')
            ->assertSee('Pagne wax')
            ->assertSee('Validée');
    }

    public function test_les_soldes_ne_deviennent_jamais_negatifs(): void
    {
        $commande = $this->commandePayee();

        // Solde en attente forcé à 0 (incohérence volontaire) avant annulation
        $this->promoteur->portefeuille->update(['solde_en_attente' => 0]);

        $this->actingAs($this->marchand)->post("/marchand/commandes/{$commande->id}/annulation");

        $this->assertSame(0, Transaction::count());
        $this->assertEquals(0, (float) $this->promoteur->portefeuille->fresh()->solde_en_attente);
        $this->assertGreaterThanOrEqual(0, (float) $this->promoteur->portefeuille->fresh()->solde_disponible);
    }
}
