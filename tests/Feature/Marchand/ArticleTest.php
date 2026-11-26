<?php

namespace Tests\Feature\Marchand;

use App\Models\Article;
use App\Models\User;
use App\Services\ImageUploader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Fakes\FakeImageUploader;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    private function fakeUploader(): FakeImageUploader
    {
        $fake = new FakeImageUploader;
        $this->app->instance(ImageUploader::class, $fake);

        return $fake;
    }

    private function marchandAvecBoutique(): User
    {
        $marchand = User::factory()->create(['role' => 'marchand']);
        $marchand->boutiques()->create(['nom' => 'Boutique test']);

        return $marchand;
    }

    private function donneesArticle(array $surcharges = []): array
    {
        return array_merge([
            'titre' => 'Pagne wax 6 yards',
            'description' => 'Tissu wax authentique',
            'prix' => 12000,
            'stock' => 10,
            'type_commission' => 'pourcentage',
            'valeur_commission' => 10,
        ], $surcharges);
    }

    public function test_marchand_cree_un_article(): void
    {
        $uploader = $this->fakeUploader();
        $marchand = $this->marchandAvecBoutique();

        $this->actingAs($marchand)->post('/marchand/articles', $this->donneesArticle([
            'photo' => UploadedFile::fake()->image('article.jpg'),
        ]))->assertRedirect(route('marchand.articles'));

        $article = Article::sole();
        $this->assertSame($marchand->boutiques()->first()->id, $article->boutique_id);
        $this->assertSame('Pagne wax 6 yards', $article->titre);
        $this->assertNotNull($article->photo_url);
        $this->assertCount(1, $uploader->envoyes);
        $this->assertSame('articles', $uploader->envoyes[0]['dossier']);
    }

    public function test_article_sans_photo_est_accepte(): void
    {
        $this->fakeUploader();
        $marchand = $this->marchandAvecBoutique();

        $this->actingAs($marchand)->post('/marchand/articles', $this->donneesArticle())
            ->assertRedirect(route('marchand.articles'));

        $this->assertNull(Article::sole()->photo_url);
        $this->assertCount(0, $this->app->make(ImageUploader::class)->envoyes ?? []);
    }

    public function test_marchand_modifie_son_article(): void
    {
        $this->fakeUploader();
        $marchand = $this->marchandAvecBoutique();
        $article = $marchand->boutiques()->first()->articles()->create(
            $this->donneesArticle(['titre' => 'Ancien titre'])
        );

        $this->actingAs($marchand)->put("/marchand/articles/{$article->id}", $this->donneesArticle([
            'titre' => 'Nouveau titre',
            'prix' => 15000,
        ]))->assertRedirect(route('marchand.articles'));

        $article->refresh();
        $this->assertSame('Nouveau titre', $article->titre);
        $this->assertSame('15000.00', $article->prix);
    }

    public function test_marchand_supprime_son_article(): void
    {
        $marchand = $this->marchandAvecBoutique();
        $article = $marchand->boutiques()->first()->articles()->create($this->donneesArticle());

        $this->actingAs($marchand)->delete("/marchand/articles/{$article->id}")
            ->assertRedirect(route('marchand.articles'));

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_un_autre_marchand_ne_peut_pas_modifier_ni_supprimer(): void
    {
        $this->fakeUploader();
        $marchandA = $this->marchandAvecBoutique();
        $marchandB = $this->marchandAvecBoutique();
        $article = $marchandA->boutiques()->first()->articles()->create($this->donneesArticle());

        $this->actingAs($marchandB)->put("/marchand/articles/{$article->id}", $this->donneesArticle())
            ->assertForbidden();
        $this->actingAs($marchandB)->delete("/marchand/articles/{$article->id}")
            ->assertForbidden();
        $this->actingAs($marchandB)->get("/marchand/articles/{$article->id}/edit")
            ->assertForbidden();

        $this->assertDatabaseHas('articles', ['id' => $article->id, 'titre' => 'Pagne wax 6 yards']);
    }

    public function test_un_promoteur_ne_peut_pas_creer_d_article(): void
    {
        $promoteur = User::factory()->create(['role' => 'promoteur']);

        $this->actingAs($promoteur)->get('/marchand/articles/create')->assertForbidden();
        $this->actingAs($promoteur)->post('/marchand/articles', $this->donneesArticle())->assertForbidden();
    }

    public function test_prix_et_stock_sont_valides(): void
    {
        $marchand = $this->marchandAvecBoutique();

        $this->actingAs($marchand)->post('/marchand/articles', $this->donneesArticle(['prix' => 0]))
            ->assertSessionHasErrors('prix');
        $this->actingAs($marchand)->post('/marchand/articles', $this->donneesArticle(['stock' => -1]))
            ->assertSessionHasErrors('stock');
    }

    public function test_commission_pourcentage_hors_limites_est_refusee(): void
    {
        $marchand = $this->marchandAvecBoutique();

        foreach ([0, 91] as $valeur) {
            $this->actingAs($marchand)->post('/marchand/articles', $this->donneesArticle([
                'type_commission' => 'pourcentage',
                'valeur_commission' => $valeur,
            ]))->assertSessionHasErrors('valeur_commission');
        }
    }

    public function test_commission_fixe_superieure_au_prix_est_refusee(): void
    {
        $marchand = $this->marchandAvecBoutique();

        $this->actingAs($marchand)->post('/marchand/articles', $this->donneesArticle([
            'type_commission' => 'fixe',
            'valeur_commission' => 12000,
        ]))->assertSessionHasErrors('valeur_commission');

        $this->actingAs($marchand)->post('/marchand/articles', $this->donneesArticle([
            'type_commission' => 'fixe',
            'valeur_commission' => 11999,
        ]))->assertSessionDoesntHaveErrors();
    }

    public function test_commission_fixe_valide_est_acceptee(): void
    {
        $marchand = $this->marchandAvecBoutique();

        $this->actingAs($marchand)->post('/marchand/articles', $this->donneesArticle([
            'type_commission' => 'fixe',
            'valeur_commission' => 500,
        ]))->assertRedirect(route('marchand.articles'));

        $this->assertSame('fixe', Article::sole()->type_commission);
    }

    public function test_creation_sans_boutique_redirige_vers_le_formulaire(): void
    {
        $marchand = User::factory()->create(['role' => 'marchand']);

        $this->actingAs($marchand)->get('/marchand/articles/create')
            ->assertRedirect(route('marchand.boutique.edit'));
        $this->actingAs($marchand)->post('/marchand/articles', $this->donneesArticle())
            ->assertRedirect(route('marchand.boutique.edit'));
    }
}
