<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Boutique;
use App\Models\Commande;
use App\Models\LienPromoteur;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Paiement\FedaPayClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeFedaPayClient;
use Tests\Fakes\FakeFedaPayTransaction;
use Tests\TestCase;

class CommandePaiementTest extends TestCase
{
    use RefreshDatabase;

    private Article $article;

    private LienPromoteur $lien;

    private User $promoteur;

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

        $this->promoteur = User::factory()->create([
            'role' => 'promoteur',
            'telephone' => '+229 97 00 00 01',
        ]);
        $this->lien = LienPromoteur::create([
            'promoteur_id' => $this->promoteur->id,
            'article_id' => $this->article->id,
            'code' => LienPromoteur::genererCode(),
        ]);
    }

    private function donneesCommande(array $surcharges = []): array
    {
        return array_merge([
            'nom' => 'Achat Test',
            'telephone' => '+229 90 11 22 33',
            'adresse_livraison' => 'Cotonou, Haie Vive',
            'quantite' => 1,
        ], $surcharges);
    }

    public function test_commande_via_cookie_valide_puis_paiement_cree_la_commission(): void
    {
        $this->withCookie('ref', $this->lien->code)
            ->post("/p/{$this->article->id}/commander", $this->donneesCommande(['quantite' => 2]))
            ->assertRedirect();

        $commande = Commande::sole();

        $this->assertSame('en_attente_paiement', $commande->statut);
        $this->assertSame($this->lien->id, $commande->lien_id);
        $this->assertEquals(24000, (float) $commande->montant_total);

        $ligne = $commande->lignes()->sole();
        $this->assertEquals(12000, (float) $ligne->prix_unitaire);
        $this->assertSame(2, (int) $ligne->quantite);
        $this->assertEquals(2400, (float) $ligne->commission);

        // Le stock n'est pas décrémenté avant le paiement
        $this->assertSame(10, (int) $this->article->fresh()->stock);

        $this->post("/commande/{$commande->id}/paiement")->assertRedirect(route('commande.confirmation', $commande));

        $commande->refresh();
        $this->assertSame('payee', $commande->statut);
        $this->assertSame(8, (int) $this->article->fresh()->stock);

        $transaction = Transaction::sole();
        $this->assertSame('commission', $transaction->type);
        $this->assertSame('en_attente', $transaction->statut);
        $this->assertEquals(2400, (float) $transaction->montant);
        $this->assertSame($this->promoteur->portefeuille->id, $transaction->portefeuille_id);
        $this->assertEquals(2400, (float) $this->promoteur->portefeuille->fresh()->solde_en_attente);
    }

    public function test_sans_cookie_aucune_commission(): void
    {
        $this->post("/p/{$this->article->id}/commander", $this->donneesCommande())
            ->assertRedirect();

        $commande = Commande::sole();
        $this->assertNull($commande->lien_id);
        $this->assertEquals(0, (float) $commande->lignes()->sole()->commission);

        $this->post("/commande/{$commande->id}/paiement");

        $this->assertSame('payee', $commande->fresh()->statut);
        $this->assertSame(0, Transaction::count());
        $this->assertEquals(0, (float) $this->promoteur->portefeuille->fresh()->solde_en_attente);
    }

    public function test_le_cookie_dun_autre_article_est_ignore(): void
    {
        $autre = Article::create([
            'boutique_id' => $this->article->boutique_id,
            'titre' => 'Autre article',
            'prix' => 5000,
            'stock' => 5,
            'type_commission' => 'fixe',
            'valeur_commission' => 500,
        ]);
        $lienAutre = LienPromoteur::create([
            'promoteur_id' => $this->promoteur->id,
            'article_id' => $autre->id,
            'code' => LienPromoteur::genererCode(),
        ]);

        // Cookie du lien de l'autre article, commande sur $this->article
        $this->withCookie('ref', $lienAutre->code)
            ->post("/p/{$this->article->id}/commander", $this->donneesCommande())
            ->assertRedirect();

        $commande = Commande::sole();
        $this->assertNull($commande->lien_id);
        $this->assertEquals(0, (float) $commande->lignes()->sole()->commission);
    }

    public function test_auto_commission_a_zero_si_meme_telephone(): void
    {
        $this->withCookie('ref', $this->lien->code)
            ->post("/p/{$this->article->id}/commander", $this->donneesCommande([
                'telephone' => '+22997000001', // même numéro, format différent
            ]))
            ->assertRedirect();

        $commande = Commande::sole();
        $this->assertSame($this->lien->id, $commande->lien_id); // lien gardé
        $this->assertEquals(0, (float) $commande->lignes()->sole()->commission);

        $this->post("/commande/{$commande->id}/paiement");

        $this->assertSame('payee', $commande->fresh()->statut);
        $this->assertSame(0, Transaction::count());
    }

    public function test_auto_commission_a_zero_si_promoteur_connecte(): void
    {
        $this->actingAs($this->promoteur)
            ->withCookie('ref', $this->lien->code)
            ->post("/p/{$this->article->id}/commander", $this->donneesCommande())
            ->assertRedirect();

        $commande = Commande::sole();
        $this->assertSame($this->lien->id, $commande->lien_id);
        $this->assertEquals(0, (float) $commande->lignes()->sole()->commission);
    }

    public function test_double_confirmation_ne_compte_quune_seule_fois(): void
    {
        $this->withCookie('ref', $this->lien->code)
            ->post("/p/{$this->article->id}/commander", $this->donneesCommande(['quantite' => 2]))
            ->assertRedirect();
        $commande = Commande::sole();

        $this->post("/commande/{$commande->id}/paiement");
        $this->post("/commande/{$commande->id}/paiement");
        $this->post("/commande/{$commande->id}/paiement");

        $this->assertSame(1, Transaction::count());
        $this->assertEquals(2400, (float) $this->promoteur->portefeuille->fresh()->solde_en_attente);
        $this->assertSame(8, (int) $this->article->fresh()->stock);
    }

    public function test_quantite_superieure_au_stock_est_refusee(): void
    {
        $this->post("/p/{$this->article->id}/commander", $this->donneesCommande(['quantite' => 11]))
            ->assertSessionHasErrors('quantite');

        $this->assertSame(0, Commande::count());
    }

    public function test_stock_insuffisant_au_paiement_annule_la_commande(): void
    {
        $this->withCookie('ref', $this->lien->code)
            ->post("/p/{$this->article->id}/commander", $this->donneesCommande(['quantite' => 2]))
            ->assertRedirect();
        $commande = Commande::sole();

        // Le stock a été écoulé entre-temps
        $this->article->update(['stock' => 1]);

        $this->post("/commande/{$commande->id}/paiement")->assertRedirect(route('commande.echec', $commande));

        $this->assertSame('annulee', $commande->fresh()->statut);
        $this->assertSame(0, Transaction::count());
        $this->assertEquals(0, (float) $this->promoteur->portefeuille->fresh()->solde_en_attente);
        $this->assertSame(1, (int) $this->article->fresh()->stock);
    }

    public function test_le_bouton_echec_annule_la_commande(): void
    {
        $this->post("/p/{$this->article->id}/commander", $this->donneesCommande())->assertRedirect();
        $commande = Commande::sole();

        $this->post("/commande/{$commande->id}/paiement/echec")->assertRedirect(route('commande.echec', $commande));

        $this->assertSame('annulee', $commande->fresh()->statut);
        $this->assertSame(0, Transaction::count());
    }

    public function test_page_de_confirmation_affiche_le_recapitulatif(): void
    {
        $this->post("/p/{$this->article->id}/commander", $this->donneesCommande())->assertRedirect();
        $commande = Commande::sole();
        $this->post("/commande/{$commande->id}/paiement");

        $this->get("/commande/{$commande->id}/confirmation")
            ->assertOk()
            ->assertSee('Paiement confirmé')
            ->assertSee('n° '.$commande->id);
    }

    public function test_webhook_mauvaise_signature_est_rejete(): void
    {
        $this->post('/webhooks/fedapay', [], ['X-FedaPay-Signature' => 't=1,s=mauvaise'])
            ->assertForbidden();
    }

    public function test_webhook_valide_confirme_la_commande(): void
    {
        config()->set('kolporte.paiement', 'fedapay');
        config()->set('kolporte.fedapay.secret', 'secret-test');

        $client = new FakeFedaPayClient;
        $client->evenementWebhook = (object) ['name' => 'transaction.approved', 'data' => ['id' => 42]];
        $this->app->instance(FedaPayClient::class, $client);

        // Création de commande via le flux FedaPay (initier → payment_url)
        $this->withCookie('ref', $this->lien->code)
            ->post("/p/{$this->article->id}/commander", $this->donneesCommande(['quantite' => 2]))
            ->assertRedirect('https://sandbox.fedapay.com/pay/42');

        $commande = Commande::sole();
        $this->assertNotNull($commande->reference_paiement);
        $this->assertSame('en_attente_paiement', $commande->statut);

        // FedaPay confirme : la transaction relue est payée
        $client->transactionRetour = new FakeFedaPayTransaction($commande->reference_paiement);

        $this->postJson('/webhooks/fedapay', ['name' => 'transaction.approved'], [
            'X-FedaPay-Signature' => 't=1,s=valide',
        ])->assertOk();

        $this->assertSame('payee', $commande->fresh()->statut);
        $this->assertSame(8, (int) $this->article->fresh()->stock);
        $this->assertEquals(2400, (float) $this->promoteur->portefeuille->fresh()->solde_en_attente);
        $this->assertSame(1, Transaction::count());
    }

    public function test_webhook_sans_transaction_payee_ne_confirme_pas(): void
    {
        config()->set('kolporte.paiement', 'fedapay');

        $client = new FakeFedaPayClient;
        $client->evenementWebhook = (object) ['name' => 'transaction.pending', 'data' => ['id' => 42]];
        $client->transactionRetour = new FakeFedaPayTransaction('KP-INCONNU', 'pending');
        $this->app->instance(FedaPayClient::class, $client);

        $this->postJson('/webhooks/fedapay', [], ['X-FedaPay-Signature' => 't=1,s=ok'])->assertOk();

        $this->assertSame(0, Commande::count());
    }
}
