<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Boutique;
use App\Models\Clic;
use App\Models\LienPromoteur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticlePublicTest extends TestCase
{
    use RefreshDatabase;

    private Article $article;

    private LienPromoteur $lien;

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

        $promoteur = User::factory()->create(['role' => 'promoteur']);
        $this->lien = LienPromoteur::create([
            'promoteur_id' => $promoteur->id,
            'article_id' => $this->article->id,
            'code' => LienPromoteur::genererCode(),
        ]);
    }

    public function test_un_ref_valide_enregistre_un_clic_et_pose_le_cookie(): void
    {
        $reponse = $this->get("/p/{$this->article->id}?ref={$this->lien->code}")
            ->assertOk()
            ->assertSee('le prix reste le même pour vous');

        $this->assertDatabaseHas('clics', [
            'lien_id' => $this->lien->id,
            'adresse_ip' => '127.0.0.1',
        ]);
        $reponse->assertCookie('ref', $this->lien->code);
    }

    public function test_le_cookie_ref_dure_7_jours(): void
    {
        $reponse = $this->get("/p/{$this->article->id}?ref={$this->lien->code}");

        $cookie = collect($reponse->headers->getCookies())->first(fn ($c) => $c->getName() === 'ref');
        $this->assertNotNull($cookie);
        $this->assertEqualsWithDelta(
            now()->addDays(7)->getTimestamp(),
            $cookie->getExpiresTime(),
            60
        );
    }

    public function test_un_second_clic_de_la_meme_ip_sous_24h_nest_pas_recompte(): void
    {
        $url = "/p/{$this->article->id}?ref={$this->lien->code}";

        $this->get($url);
        $this->get($url);
        $this->get($url);

        $this->assertSame(1, Clic::count());
    }

    public function test_le_clic_est_recompte_apres_24h(): void
    {
        $ancien = Clic::create(['lien_id' => $this->lien->id, 'adresse_ip' => '127.0.0.1']);
        Clic::whereKey($ancien->id)->update(['created_at' => now()->subHours(25)]);

        $this->get("/p/{$this->article->id}?ref={$this->lien->code}");

        $this->assertSame(2, Clic::count());
    }

    public function test_un_code_inconnu_est_ignore(): void
    {
        $this->get("/p/{$this->article->id}?ref=INCONNU")
            ->assertOk()
            ->assertDontSee('Vous passez par le lien');

        $this->assertSame(0, Clic::count());
    }

    public function test_le_code_dun_autre_article_est_ignore(): void
    {
        $autre = Article::create([
            'boutique_id' => $this->article->boutique_id,
            'titre' => 'Autre article',
            'prix' => 5000,
            'stock' => 3,
            'type_commission' => 'fixe',
            'valeur_commission' => 500,
        ]);

        $this->get("/p/{$autre->id}?ref={$this->lien->code}")
            ->assertOk()
            ->assertDontSee('Vous passez par le lien');

        $this->assertSame(0, Clic::count());
    }

    public function test_un_robot_ne_genere_pas_de_clic(): void
    {
        foreach (['Mozilla/5.0 (compatible; Googlebot/2.1)', 'facebookexternalhit/1.1', 'WhatsApp/2.23'] as $agent) {
            Clic::query()->delete();
            $this->withHeader('User-Agent', $agent)
                ->get("/p/{$this->article->id}?ref={$this->lien->code}")
                ->assertOk();
            $this->assertSame(0, Clic::count(), "Le robot {$agent} ne doit pas générer de clic");
        }
    }

    public function test_article_epuise_affiche_le_message_sans_formulaire(): void
    {
        $this->article->update(['stock' => 0]);

        $this->get("/p/{$this->article->id}")
            ->assertOk()
            ->assertSee('Article épuisé')
            ->assertDontSee('zone-commande');
    }

    public function test_sans_ref_valide_pas_dencadre_ni_de_clic(): void
    {
        $this->get("/p/{$this->article->id}")
            ->assertOk()
            ->assertDontSee('Vous passez par le lien')
            ->assertSee('zone-commande'); // emplacement réservé au formulaire (étape 5)

        $this->assertSame(0, Clic::count());
    }
}
