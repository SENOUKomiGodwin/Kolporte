<?php

namespace Tests\Feature\Marchand;

use App\Models\User;
use App\Services\ImageUploader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Fakes\FakeImageUploader;
use Tests\TestCase;

class BoutiqueTest extends TestCase
{
    use RefreshDatabase;

    private function fakeUploader(): FakeImageUploader
    {
        $fake = new FakeImageUploader;
        $this->app->instance(ImageUploader::class, $fake);

        return $fake;
    }

    public function test_marchand_cree_sa_boutique(): void
    {
        $uploader = $this->fakeUploader();
        $marchand = User::factory()->create(['role' => 'marchand']);

        $reponse = $this->actingAs($marchand)->put('/marchand/boutique', [
            'nom' => 'Chez Akossiwa',
            'description' => 'Pagne wax et tissus',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $reponse->assertRedirect(route('marchand'));
        $this->assertDatabaseHas('boutiques', [
            'marchand_id' => $marchand->id,
            'nom' => 'Chez Akossiwa',
        ]);
        $this->assertCount(1, $uploader->envoyes);
        $this->assertSame('logos', $uploader->envoyes[0]['dossier']);
        $this->assertNotNull($marchand->boutiques()->first()->logo_url);
    }

    public function test_marchand_modifie_sa_boutique(): void
    {
        $this->fakeUploader();
        $marchand = User::factory()->create(['role' => 'marchand']);
        $boutique = $marchand->boutiques()->create(['nom' => 'Ancien nom']);

        $this->actingAs($marchand)->put('/marchand/boutique', [
            'nom' => 'Nouveau nom',
            'description' => null,
        ])->assertRedirect(route('marchand'));

        $this->assertSame('Nouveau nom', $boutique->fresh()->nom);
    }

    public function test_tableau_de_bord_invite_a_creer_la_boutique(): void
    {
        $marchand = User::factory()->create(['role' => 'marchand']);

        $this->actingAs($marchand)->get('/marchand')
            ->assertOk()
            ->assertSee('Créez votre boutique');
    }

    public function test_invite_aussi_sur_la_page_articles(): void
    {
        $marchand = User::factory()->create(['role' => 'marchand']);

        $this->actingAs($marchand)->get('/marchand/articles')
            ->assertOk()
            ->assertSee('rattachés à une boutique');
    }

    public function test_seul_un_marchand_peut_gerer_une_boutique(): void
    {
        $promoteur = User::factory()->create(['role' => 'promoteur']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($promoteur)->get('/marchand/boutique')->assertForbidden();
        $this->actingAs($promoteur)->put('/marchand/boutique', ['nom' => 'Test'])->assertForbidden();
        $this->actingAs($admin)->get('/marchand/boutique')->assertForbidden();
    }

    public function test_nom_est_requis(): void
    {
        $marchand = User::factory()->create(['role' => 'marchand']);

        $this->actingAs($marchand)->put('/marchand/boutique', [
            'nom' => '',
        ])->assertSessionHasErrors('nom');
    }
}
