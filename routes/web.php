<?php

use App\Http\Controllers\ArticlePublicController;
use App\Http\Controllers\Marchand\ArticleController;
use App\Http\Controllers\Marchand\BoutiqueController;
use App\Http\Controllers\Marchand\TableauDeBordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Promoteur\ArticleController as PromoteurArticleController;
use App\Http\Controllers\Promoteur\CatalogueController;
use App\Http\Controllers\Promoteur\LiensController;
use App\Models\Article;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    return $user ? redirect($user->cheminEspace()) : redirect()->route('login');
});

// Espaces par rôle (contenu des pages dans les étapes suivantes)
Route::get('/promoteur', CatalogueController::class)
    ->middleware(['auth', 'role:promoteur'])->name('promoteur');
Route::get('/promoteur/articles/{article}', [PromoteurArticleController::class, 'show'])
    ->middleware(['auth', 'role:promoteur'])->name('promoteur.article');
Route::post('/promoteur/articles/{article}/lien', [PromoteurArticleController::class, 'genererLien'])
    ->middleware(['auth', 'role:promoteur'])->name('promoteur.article.lien');
Route::get('/promoteur/liens', [LiensController::class, 'index'])
    ->middleware(['auth', 'role:promoteur'])->name('promoteur.liens');
Route::get('/promoteur/portefeuille', fn () => view('espace.page', ['titre' => 'Portefeuille', 'actif' => 'portefeuille']))
    ->middleware(['auth', 'role:promoteur'])->name('promoteur.portefeuille');

// Page publique de l'article (contenu à l'étape 4)
Route::get('/p/{article}', ArticlePublicController::class)
    ->name('article.public');

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
Route::get('/marchand/commandes', fn () => view('espace.page', ['titre' => 'Commandes', 'actif' => 'commandes']))
    ->middleware(['auth', 'role:marchand'])->name('marchand.commandes');

Route::get('/admin', fn () => view('espace.page', ['titre' => 'Administration', 'actif' => null]))
    ->middleware(['auth', 'role:admin'])->name('admin');
Route::get('/admin/retraits', fn () => view('espace.page', ['titre' => 'Retraits', 'actif' => 'retraits']))
    ->middleware(['auth', 'role:admin'])->name('admin.retraits');
Route::get('/admin/utilisateurs', fn () => view('espace.page', ['titre' => 'Utilisateurs', 'actif' => 'utilisateurs']))
    ->middleware(['auth', 'role:admin'])->name('admin.utilisateurs');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
