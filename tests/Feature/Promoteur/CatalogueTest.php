<?php

namespace Tests\Feature\Promoteur;

use App\Models\Article;
use App\Models\Boutique;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogueTest extends TestCase
{
    use RefreshDatabase;

    private function article(array $surcharges = []): Article
    {
        static $i = 0;
        $i++;

        $marchand = User::factory()->create(['role' => 'marchand']);
        $boutique = Boutique::create(['marchand_id' => $marchand->id, 'nom' => 'Boutique '.$i]);

        return Article::create(array_merge([
            'boutique_id' => $boutique->id,
            'titre' => 'Article '.$i,
            'prix' => 10000,
            'stock' => 5,
            'type_commission' => 'pourcentage',
            'valeur_commission' => 10,
        ], $surcharges));
    }

    public function test_le_catalogue_affiche_les_articles_en_stock(): void
    {
        $enStock = $this->article(['titre' => 'Pagne wax en stock']);
        $this->article(['titre' => 'Article épuisé', 'stock' => 0]);

        $promoteur = User::factory()->create(['role' => 'promoteur']);

        $this->actingAs($promoteur)->get('/promoteur')
            ->assertOk()
            ->assertSee('Pagne wax en stock')
            ->assertDontSee('Article épuisé')
            ->assertSee('1 000 FCFA'); // gain : 10 % de 10 000
    }

    public function test_la_recherche_filtre_par_titre(): void
    {
        $this->article(['titre' => 'Sac en cuir']);
        $this->article(['titre' => 'Pagne wax']);

        $promoteur = User::factory()->create(['role' => 'promoteur']);

        $this->actingAs($promoteur)->get('/promoteur?q=cuir')
            ->assertOk()
            ->assertSee('Sac en cuir')
            ->assertDontSee('Pagne wax');
    }

    public function test_un_marchand_ne_peut_pas_voir_le_catalogue(): void
    {
        $marchand = User::factory()->create(['role' => 'marchand']);

        $this->actingAs($marchand)->get('/promoteur')->assertForbidden();
        $this->actingAs($marchand)->get('/promoteur/liens')->assertForbidden();
    }

    public function test_invite_sans_resultat(): void
    {
        $promoteur = User::factory()->create(['role' => 'promoteur']);

        $this->actingAs($promoteur)->get('/promoteur?q=introuvable')
            ->assertOk()
            ->assertSee('Aucun article ne correspond');
    }
}
