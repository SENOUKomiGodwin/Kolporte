<?php

namespace Tests\Feature\Marchand;

use App\Models\Article;
use App\Models\Boutique;
use App\Models\Commande;
use App\Models\LienPromoteur;
use App\Models\LigneCommande;
use App\Models\Transaction;
use App\Models\User;
use App\Services\StatistiquesMarchand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableauDeBordTest extends TestCase
{
    use RefreshDatabase;

    private Boutique $boutique;

    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        $marchand = User::factory()->create(['role' => 'marchand']);
        $this->boutique = Boutique::create(['marchand_id' => $marchand->id, 'nom' => 'Chez Akossiwa']);
        $this->article = Article::create([
            'boutique_id' => $this->boutique->id,
            'titre' => 'Pagne wax',
            'prix' => 10000,
            'stock' => 20,
            'type_commission' => 'pourcentage',
            'valeur_commission' => 10,
        ]);
    }

    private function commandePayee(LienPromoteur $lien, string $statut, int $quantite, ?\DateTimeInterface $date = null): Commande
    {
        $commande = Commande::create([
            'lien_id' => $lien->id,
            'nom_acheteur' => 'Acheteur test',
            'telephone_acheteur' => '90000000',
            'adresse_livraison' => 'Cotonou',
            'montant_total' => 10000 * $quantite,
            'statut' => $statut,
        ]);

        LigneCommande::create([
            'commande_id' => $commande->id,
            'article_id' => $this->article->id,
            'quantite' => $quantite,
            'prix_unitaire' => 10000,
            'commission' => 1000 * $quantite,
        ]);

        if ($date) {
            $commande->created_at = $date;
            $commande->save();
        }

        return $commande;
    }

    private function promoteurAvecLien(string $nom): array
    {
        $promoteur = User::factory()->create(['role' => 'promoteur', 'name' => $nom]);
        $lien = LienPromoteur::create([
            'promoteur_id' => $promoteur->id,
            'article_id' => $this->article->id,
            'code' => LienPromoteur::genererCode(),
        ]);

        return [$promoteur, $lien];
    }

    public function test_tableau_de_bord_sans_donnees(): void
    {
        $stats = app(StatistiquesMarchand::class)->calculer($this->boutique);

        $this->assertSame(0.0, $stats['ventes_mois']);
        $this->assertSame(0, $stats['commandes_mois']);
        $this->assertSame(0.0, $stats['commissions_versees']);
        $this->assertSame(0, $stats['promoteurs_actifs']);
        $this->assertTrue($stats['commandes_a_valider']->isEmpty());
        $this->assertTrue($stats['meilleurs_promoteurs']->isEmpty());
    }

    public function test_les_chiffres_se_calculent_sur_les_bonnes_commandes(): void
    {
        [$p1, $lien1] = $this->promoteurAvecLien('Koffi');
        [$p2, $lien2] = $this->promoteurAvecLien('Ama');
        [$p3, $lien3] = $this->promoteurAvecLien('Yao');

        // 2 ventes ce mois : 20 000 + 10 000 FCFA
        $c1 = $this->commandePayee($lien1, 'payee', 2);
        $c2 = $this->commandePayee($lien2, 'livree', 1);

        // Exclues : en attente de paiement, annulée, et commande du mois dernier
        $this->commandePayee($lien3, 'en_attente_paiement', 5);
        $this->commandePayee($lien3, 'annulee', 5);
        $cAncienne = $this->commandePayee($lien1, 'payee', 7, now()->subMonth());

        // Une commission validée (compte) et une en attente (ne compte pas)
        Transaction::create([
            'portefeuille_id' => $p1->portefeuille->id,
            'commande_id' => $c1->id,
            'type' => 'commission',
            'montant' => 2000,
            'statut' => 'validee',
        ]);
        Transaction::create([
            'portefeuille_id' => $p2->portefeuille->id,
            'commande_id' => $c2->id,
            'type' => 'commission',
            'montant' => 1000,
            'statut' => 'en_attente',
        ]);
        // Une transaction de retrait ne doit pas compter
        Transaction::create([
            'portefeuille_id' => $p1->portefeuille->id,
            'commande_id' => null,
            'type' => 'retrait',
            'montant' => 500,
            'statut' => 'payee',
        ]);

        $stats = app(StatistiquesMarchand::class)->calculer($this->boutique);

        $this->assertSame(30000.0, $stats['ventes_mois']);
        $this->assertSame(2, $stats['commandes_mois']);
        $this->assertSame(2000.0, $stats['commissions_versees']);
        // Yao n'a que des commandes non payées : seuls Koffi et Ama sont actifs
        $this->assertSame(2, $stats['promoteurs_actifs']);

        // Les deux commandes payee non livrées sont à valider, même celle du mois dernier
        $this->assertCount(2, $stats['commandes_a_valider']);
        $this->assertEqualsCanonicalizing(
            [$c1->id, $cAncienne->id],
            $stats['commandes_a_valider']->pluck('id')->all()
        );

        // Classement par nombre de ventes (payee|livree, tous mois) : Koffi 2, Ama 1
        $this->assertCount(2, $stats['meilleurs_promoteurs']);
        $this->assertSame($p1->id, $stats['meilleurs_promoteurs']->first()->promoteur_id);
        $this->assertSame(2, $stats['meilleurs_promoteurs']->first()->ventes);
        $this->assertSame($p2->id, $stats['meilleurs_promoteurs']->get(1)->promoteur_id);
    }

    public function test_les_ventes_d_une_autre_boutique_ne_sont_pas_comptees(): void
    {
        $autre = User::factory()->create(['role' => 'marchand']);
        $autreBoutique = Boutique::create(['marchand_id' => $autre->id, 'nom' => 'Autre']);
        $autreArticle = Article::create([
            'boutique_id' => $autreBoutique->id,
            'titre' => 'Autre article',
            'prix' => 5000,
            'stock' => 3,
            'type_commission' => 'fixe',
            'valeur_commission' => 500,
        ]);

        [$p, $lien] = $this->promoteurAvecLien('Test');
        $lien->update(['article_id' => $autreArticle->id]);

        $commande = Commande::create([
            'lien_id' => $lien->id,
            'nom_acheteur' => 'X',
            'telephone_acheteur' => '1',
            'adresse_livraison' => 'Y',
            'montant_total' => 5000,
            'statut' => 'payee',
        ]);
        LigneCommande::create([
            'commande_id' => $commande->id,
            'article_id' => $autreArticle->id,
            'quantite' => 1,
            'prix_unitaire' => 5000,
            'commission' => 500,
        ]);

        $stats = app(StatistiquesMarchand::class)->calculer($this->boutique);

        $this->assertSame(0.0, $stats['ventes_mois']);
        $this->assertSame(0, $stats['promoteurs_actifs']);
    }

    public function test_la_page_marchand_affiche_le_tableau_de_bord(): void
    {
        $marchand = User::where('role', 'marchand')->first();

        $this->actingAs($marchand)->get('/marchand')
            ->assertOk()
            ->assertSee('Chez Akossiwa')
            ->assertSee('Ventes du mois')
            ->assertSee('Meilleurs promoteurs');
    }
}
