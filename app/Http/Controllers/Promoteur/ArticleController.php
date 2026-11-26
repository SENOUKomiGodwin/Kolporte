<?php

namespace App\Http\Controllers\Promoteur;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\LienPromoteur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(Request $request, Article $article): View
    {
        $lien = $request->user()->liens()
            ->where('article_id', $article->id)
            ->first();

        return view('promoteur.article', [
            'article' => $article->load('boutique'),
            'lien' => $lien,
            'url' => $lien ? route('article.public', ['article' => $article, 'ref' => $lien->code]) : null,
        ]);
    }

    public function genererLien(Request $request, Article $article): RedirectResponse
    {
        $request->user()->liens()->firstOrCreate(
            ['article_id' => $article->id],
            ['code' => LienPromoteur::genererCode()]
        );

        return redirect()->route('promoteur.article', $article)
            ->with('statut', 'Votre lien de partage est prêt.');
    }
}
