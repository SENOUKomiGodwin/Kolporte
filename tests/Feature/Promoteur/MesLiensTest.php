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

class MesLiensTest extends TestCase
{
    use RefreshDatabase;

    public function test_mes_liens_est_trie_par_gains(): void
    {
        $promoteur = User::factory()->create(['role' => 'promoteur']);
        $marchand = User::factory()->create(['role' => 'marchand']);
        $boutique = Boutique::create(['marchand_id' => $marchand->id, 'nom' => 'Boutique']);

        $articles = collect(['Article A', 'Article B'])->map(fn ($titre) => Article::create([
            'boutique_id' => $boutique->id,
            'titre' => $titre,
            'prix' => 10000,
            'stock' => 5,
            'type_commission' => 'pourcentage',
            'valeur_commission' => 10,
        ]));

        // Lien A : 1 vente avec commission validée (1000)
        $lienA = LienPromoteur::create([
            'promoteur_id' => $promoteur->id,
            'article_id' => $articles[0]->id,
            'code' => LienPromoteur::genererCode(),
        ]);
        $commandeA = Commande::create([
            'lien_id' => $lienA->id,
            'nom_acheteur' => 'A',
            'telephone_acheteur' => '1',
            'adresse_livraison' => 'X',
            'montant_total' => 10000,
            'statut' => 'payee',
        ]);
        Transaction::create([
            'portefeuille_id' => $promoteur->portefeuille->id,
            'commande_id' => $commandeA->id,
            'type' => 'commission',
            'montant' => 1000,
            'statut' => 'validee',
        ]);
        Clic::create(['lien_id' => $lienA->id, 'adresse_ip' => '1.1.1.1']);

        // Lien B : aucun gain
        LienPromoteur::create([
            'promoteur_id' => $promoteur->id,
            'article_id' => $articles[1]->id,
            'code' => LienPromoteur::genererCode(),
        ]);

        $reponse = $this->actingAs($promoteur)->get('/promoteur/liens')->assertOk();

        $contenu = $reponse->getContent();
        $this->assertTrue(
            strpos($contenu, 'Article A') < strpos($contenu, 'Article B'),
            'Le lien avec le plus de gains doit apparaître en premier'
        );
    }

    public function test_mes_liens_affiche_les_statistiques_meme_a_zero(): void
    {
        $promoteur = User::factory()->create(['role' => 'promoteur']);
        $marchand = User::factory()->create(['role' => 'marchand']);
        $boutique = Boutique::create(['marchand_id' => $marchand->id, 'nom' => 'Boutique']);
        $article = Article::create([
            'boutique_id' => $boutique->id,
            'titre' => 'Article test',
            'prix' => 5000,
            'stock' => 2,
            'type_commission' => 'fixe',
            'valeur_commission' => 500,
        ]);
        LienPromoteur::create([
            'promoteur_id' => $promoteur->id,
            'article_id' => $article->id,
            'code' => LienPromoteur::genererCode(),
        ]);

        $this->actingAs($promoteur)->get('/promoteur/liens')
            ->assertOk()
            ->assertSee('Article test')
            ->assertSee('Clics')
            ->assertSee('Ventes')
            ->assertSee('Gains FCFA');
    }
}
