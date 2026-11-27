<?php

use App\Http\Controllers\Admin\RetraitController;
use App\Http\Controllers\Admin\UtilisateurController;
use App\Http\Controllers\ArticlePublicController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\FedaPayWebhookController;
use App\Http\Controllers\Marchand\ArticleController;
use App\Http\Controllers\Marchand\BoutiqueController;
use App\Http\Controllers\Marchand\CommandeController as MarchandCommandeController;
use App\Http\Controllers\Marchand\TableauDeBordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Promoteur\ArticleController as PromoteurArticleController;
use App\Http\Controllers\Promoteur\CatalogueController;
use App\Http\Controllers\Promoteur\LiensController;
use App\Http\Controllers\Promoteur\PortefeuilleController as PromoteurPortefeuilleController;
use App\Models\Article;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    return $user ? redirect($user->cheminEspace()) : redirect()->route('login');
});

// Page de repli hors-ligne de la PWA (mise en cache par le service worker)
Route::view('/hors-ligne', 'hors-ligne')->name('hors.ligne');

// Fichiers PWA : en production le serveur web les sert directement depuis public/,
// ces routes servent de repli (tests et environnements sans docroot public).
Route::get('/manifest.webmanifest', fn () => response()->file(public_path('manifest.webmanifest'), [
    'Content-Type' => 'application/manifest+json',
]))->name('pwa.manifest');
Route::get('/sw.js', fn () => response()->file(public_path('sw.js'), [
    'Content-Type' => 'application/javascript',
]))->name('pwa.sw');

// Espaces par rôle (contenu des pages dans les étapes suivantes)
Route::get('/promoteur', CatalogueController::class)
    ->middleware(['auth', 'role:promoteur'])->name('promoteur');
Route::get('/promoteur/articles/{article}', [PromoteurArticleController::class, 'show'])
    ->middleware(['auth', 'role:promoteur'])->name('promoteur.article');
Route::post('/promoteur/articles/{article}/lien', [PromoteurArticleController::class, 'genererLien'])
    ->middleware(['auth', 'role:promoteur'])->name('promoteur.article.lien');
Route::get('/promoteur/liens', [LiensController::class, 'index'])
    ->middleware(['auth', 'role:promoteur'])->name('promoteur.liens');
Route::get('/promoteur/portefeuille', [PromoteurPortefeuilleController::class, 'index'])
    ->middleware(['auth', 'role:promoteur'])->name('promoteur.portefeuille');
Route::post('/promoteur/portefeuille/retrait', [PromoteurPortefeuilleController::class, 'demander'])
    ->middleware(['auth', 'role:promoteur'])->name('promoteur.portefeuille.retrait');

// Page publique de l'article
Route::get('/p/{article}', ArticlePublicController::class)
    ->name('article.public');
Route::post('/p/{article}/commander', [CommandeController::class, 'store'])
    ->name('commande.store');
Route::get('/commande/{commande}/paiement', [CommandeController::class, 'paiement'])
    ->name('commande.paiement');
Route::post('/commande/{commande}/paiement', [CommandeController::class, 'payer'])
    ->name('commande.paiement.confirmer');
Route::post('/commande/{commande}/paiement/echec', [CommandeController::class, 'echecPaiement'])
    ->name('commande.paiement.echec');
Route::get('/commande/{commande}/confirmation', [CommandeController::class, 'confirmation'])
    ->name('commande.confirmation');
Route::get('/commande/{commande}/echec', [CommandeController::class, 'echec'])
    ->name('commande.echec');
Route::get('/paiement/fedapay/retour', [CommandeController::class, 'fedapayRetour'])
    ->name('paiement.fedapay.retour');
Route::post('/webhooks/fedapay', FedaPayWebhookController::class)
    ->name('webhooks.fedapay');

Route::get('/marchand', TableauDeBordController::class)
    ->middleware(['auth', 'role:marchand'])->name('marchand');
Route::get('/marchand/boutique', [BoutiqueController::class, 'edit'])
    ->middleware(['auth', 'role:marchand'])->name('marchand.boutique.edit');
Route::put('/marchand/boutique', [BoutiqueController::class, 'update'])
    ->middleware(['auth', 'role:marchand'])->name('marchand.boutique.update');
Route::get('/marchand/articles', [ArticleController::class, 'index'])
    ->middleware(['auth', 'role:marchand'])->name('marchand.articles');
Route::get('/marchand/articles/create', [ArticleController::class, 'create'])
    ->middleware(['auth', 'role:marchand'])->name('marchand.articles.create');
Route::post('/marchand/articles', [ArticleController::class, 'store'])
    ->middleware(['auth', 'role:marchand'])->name('marchand.articles.store');
Route::get('/marchand/articles/{article}/edit', [ArticleController::class, 'edit'])
    ->middleware(['auth', 'role:marchand'])->name('marchand.articles.edit');
Route::put('/marchand/articles/{article}', [ArticleController::class, 'update'])
    ->middleware(['auth', 'role:marchand'])->name('marchand.articles.update');
Route::delete('/marchand/articles/{article}', [ArticleController::class, 'destroy'])
    ->middleware(['auth', 'role:marchand'])->name('marchand.articles.destroy');
Route::get('/marchand/commandes', [MarchandCommandeController::class, 'index'])
    ->middleware(['auth', 'role:marchand'])->name('marchand.commandes');
Route::get('/marchand/commandes/{commande}', [MarchandCommandeController::class, 'show'])
    ->middleware(['auth', 'role:marchand'])->name('marchand.commandes.show');
Route::post('/marchand/commandes/{commande}/livraison', [MarchandCommandeController::class, 'livrer'])
    ->middleware(['auth', 'role:marchand'])->name('marchand.commandes.livrer');
Route::post('/marchand/commandes/{commande}/annulation', [MarchandCommandeController::class, 'annuler'])
    ->middleware(['auth', 'role:marchand'])->name('marchand.commandes.annuler');

Route::get('/admin', fn () => view('espace.page', ['titre' => 'Administration', 'actif' => null]))
    ->middleware(['auth', 'role:admin'])->name('admin');
Route::get('/admin/retraits', [RetraitController::class, 'index'])
    ->middleware(['auth', 'role:admin'])->name('admin.retraits');
Route::post('/admin/retraits/{demande}/valider', [RetraitController::class, 'valider'])
    ->middleware(['auth', 'role:admin'])->name('admin.retraits.valider');
Route::post('/admin/retraits/{demande}/refuser', [RetraitController::class, 'refuser'])
    ->middleware(['auth', 'role:admin'])->name('admin.retraits.refuser');
Route::get('/admin/utilisateurs', [UtilisateurController::class, 'index'])
    ->middleware(['auth', 'role:admin'])->name('admin.utilisateurs');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
