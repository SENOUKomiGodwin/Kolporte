<?php

namespace App\Http\Controllers\Promoteur;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogueController extends Controller
{
    public function __invoke(Request $request): View
    {
        $recherche = trim((string) $request->query('q', ''));

        $articles = Article::query()
            ->where('stock', '>', 0)
            ->when($recherche !== '', fn ($q) => $q->where('titre', 'like', '%'.$recherche.'%'))
            ->with('boutique')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('promoteur.catalogue', [
            'articles' => $articles,
            'recherche' => $recherche,
        ]);
    }
}
