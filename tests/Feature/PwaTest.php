<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_manifeste_est_valide_et_complet(): void
    {
        $this->get('/manifest.webmanifest')->assertOk();
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);

        $this->assertIsArray($manifest);
        $this->assertSame('Kolporte', $manifest['name']);
        $this->assertSame('Kolporte', $manifest['short_name']);
        $this->assertSame('fr', $manifest['lang']);
        $this->assertSame('/', $manifest['start_url']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#0F5C45', $manifest['theme_color']);
        $this->assertSame('#FBF7F0', $manifest['background_color']);

        $icones = collect($manifest['icons']);
        $this->assertTrue($icones->contains(fn ($i) => $i['sizes'] === '192x192'));
        $this->assertTrue($icones->contains(fn ($i) => $i['sizes'] === '512x512' && $i['purpose'] === 'any'));
        $this->assertTrue($icones->contains(fn ($i) => $i['purpose'] === 'maskable'));

        foreach ($icones as $icone) {
            $this->assertFileExists(public_path(ltrim($icone['src'], '/')));
        }
    }

    public function test_les_icones_existent_et_sont_des_png(): void
    {
        foreach (['icon-192.png', 'icon-512.png', 'icon-maskable-512.png'] as $fichier) {
            $chemin = public_path("icons/{$fichier}");
            $this->assertFileExists($chemin);
            $this->assertSame("\x89PNG", substr(file_get_contents($chemin), 0, 4));
        }
    }

    public function test_le_service_worker_repond_en_javascript(): void
    {
        $this->get('/sw.js')
            ->assertOk()
            ->assertHeader('content-type', 'application/javascript');

        $this->assertStringContainsString('kolporte-v', file_get_contents(public_path('sw.js')));
        $this->assertStringContainsString('hors-ligne', file_get_contents(public_path('sw.js')));
    }

    public function test_la_page_hors_ligne_est_accessible(): void
    {
        $this->get('/hors-ligne')
            ->assertOk()
            ->assertSee('hors ligne')
            ->assertSee('Réessayer');
    }

    public function test_les_pages_exposent_le_manifeste(): void
    {
        $this->get('/login')->assertSee('rel="manifest"', false);
        $this->get('/hors-ligne')->assertSee('rel="manifest"', false);
    }
}
