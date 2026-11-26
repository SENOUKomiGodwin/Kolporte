<?php

namespace Tests\Feature\Promoteur;

use App\Models\Article;
use App\Models\Boutique;
use App\Models\Clic;
use App\Models\Commande;
use App\Models\LienPromoteur;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LienPromoteurTest extends TestCase
{
    use RefreshDatabase;

    private User $promoteur;

    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        $marchand = User::factory()->create(['role' => 'marchand']);
        $boutique = Boutique::create(['marchand_id' => $marchand->id, 'nom' => 'Chez Akossiwa']);
        $this->article = Article::create([
            'boutique_id' => $boutique->id,
            'titre' => 'Pagne wax',
            'prix' => 12000,
            'stock' => 10,
            'type_commission' => 'pourcentage',
            'valeur_commission' => 10,
        ]);
        $this->promoteur = User::factory()->create(['role' => 'promoteur']);
    }

    private function creerLien(): LienPromoteur
    {
        return LienPromoteur::create([
            'promoteur_id' => $this->promoteur->id,
            'article_id' => $this->article->id,
            'code' => LienPromoteur::genererCode(),
        ]);
    }

    private function commande(LienPromoteur $lien, string $statut): Commande
    {
        return Commande::create([
            'lien_id' => $lien->id,
            'nom_acheteur' => 'Acheteur',
            'telephone_acheteur' => '90000000',
            'adresse_livraison' => 'Cotonou',
            'montant_total' => 12000,
            'statut' => $statut,
        ]);
    }

    public function test_la_page_article_propose_de_generer_un_lien(): void
    {
        $this->actingAs($this->promoteur)->get("/promoteur/articles/{$this->article->id}")
            ->assertOk()
            ->assertSee('Générer mon lien')
            ->assertSee('1 200 FCFA'); // commission 10 % de 12 000
    }

    public function test_la_generation_est_idempotente(): void
    {
        $this->actingAs($this->promoteur)
            ->post("/promoteur/articles/{$this->article->id}/lien")
            ->assertRedirect(route('promoteur.article', $this->article));

        $this->actingAs($this->promoteur)
            ->post("/promoteur/articles/{$this->article->id}/lien");

        $this->assertSame(1, LienPromoteur::count());
    }

    public function test_le_code_est_unique_et_au_bon_format(): void
    {
        $codes = collect(range(1, 20))->map(fn () => LienPromoteur::genererCode());

        $this->assertSame(20, $codes->unique()->count());
        $codes->each(fn ($code) => $this->assertMatchesRegularExpression('/^P[A-Z0-9]{7}$/', $code));
    }

    public function test_apres_generation_le_lien_et_ses_stats_saffichent(): void
    {
        $lien = $this->creerLien();
        Clic::create(['lien_id' => $lien->id, 'adresse_ip' => '1.2.3.4']);
        Clic::create(['lien_id' => $lien->id, 'adresse_ip' => '5.6.7.8']);

        $payee = $this->commande($lien, 'payee');
        $this->commande($lien, 'en_attente_paiement'); // ne compte pas
        $this->commande($lien, 'livree');

        Transaction::create([
            'portefeuille_id' => $this->promoteur->portefeuille->id,
            'commande_id' => $payee->id,
            'type' => 'commission',
            'montant' => 1200,
            'statut' => 'validee',
        ]);
        Transaction::create([
            'portefeuille_id' => $this->promoteur->portefeuille->id,
            'commande_id' => $payee->id,
            'type' => 'commission',
            'montant' => 1200,
            'statut' => 'en_attente', // ne compte pas dans les gains
        ]);

        $this->actingAs($this->promoteur)->get("/promoteur/articles/{$this->article->id}")
            ->assertOk()
            ->assertSee("/p/{$this->article->id}?ref={$lien->code}")
            ->assertSee('Statistiques du lien')
            ->assertSee('Copier le lien')
            ->assertSee('Partager');

        $this->assertSame(2, $lien->nombreClics());
        $this->assertSame(2, $lien->nombreVentes());
        $this->assertSame(1200.0, $lien->gains());
    }

    public function test_un_autre_promoteur_ne_voit_pas_le_lien_des_autres(): void
    {
        $this->creerLien();
        $autre = User::factory()->create(['role' => 'promoteur']);

        // L'autre promoteur a sa propre page article sans lien généré
        $this->actingAs($autre)->get("/promoteur/articles/{$this->article->id}")
            ->assertOk()
            ->assertSee('Générer mon lien')
            ->assertDontSee('Statistiques du lien');

        $this->actingAs($autre)->get('/promoteur/liens')
            ->assertOk()
            ->assertDontSee($this->article->titre);
    }

    public function test_la_route_publique_article_existe(): void
    {
        $this->get("/p/{$this->article->id}?ref=TEST123")
            ->assertOk()
            ->assertSee('Pagne wax');
    }
}
