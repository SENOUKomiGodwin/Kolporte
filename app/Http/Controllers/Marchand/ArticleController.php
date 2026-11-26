<?php

namespace App\Http\Controllers\Marchand;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Article::class);

        $boutique = $request->user()->boutiques()->first();

        return view('marchand.articles.index', [
            'boutique' => $boutique,
            'articles' => $boutique?->articles()->latest()->get() ?? collect(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('create', Article::class);

        $boutique = $request->user()->boutiques()->first();

        if (! $boutique) {
            return redirect()->route('marchand.boutique.edit');
        }

        return view('marchand.articles.form', [
            'boutique' => $boutique,
            'article' => new Article(['type_commission' => 'pourcentage']),
        ]);
    }

    public function store(Request $request, ImageUploader $uploader): RedirectResponse
    {
        Gate::authorize('create', Article::class);

        $boutique = $request->user()->boutiques()->first();

        if (! $boutique) {
            return redirect()->route('marchand.boutique.edit');
        }

        $donnees = $this->valider($request);

        if ($request->hasFile('photo')) {
            $donnees['photo_url'] = $uploader->envoyer($request->file('photo'), 'articles');
        }

        $boutique->articles()->create($donnees);

        return redirect()->route('marchand.articles')->with('statut', 'Article publié.');
    }

    public function edit(Request $request, Article $article): View
    {
        Gate::authorize('update', $article);

        return view('marchand.articles.form', [
            'boutique' => $article->boutique,
            'article' => $article,
        ]);
    }

    public function update(Request $request, Article $article, ImageUploader $uploader): RedirectResponse
    {
        Gate::authorize('update', $article);

        $donnees = $this->valider($request);

        if ($request->hasFile('photo')) {
            $donnees['photo_url'] = $uploader->envoyer($request->file('photo'), 'articles');
        }

        $article->update($donnees);

        return redirect()->route('marchand.articles')->with('statut', 'Article mis à jour.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        Gate::authorize('delete', $article);

        $article->delete();

        return redirect()->route('marchand.articles')->with('statut', 'Article supprimé.');
    }

    /** @return array<string, mixed> */
    private function valider(Request $request): array
    {
        $valide = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'prix' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'type_commission' => ['required', Rule::in(['pourcentage', 'fixe'])],
            'valeur_commission' => [
                'required', 'numeric', 'min:0',
                Rule::when(
                    $request->input('type_commission') === 'pourcentage',
                    ['min:1', 'max:90'],
                    ['lt:prix']
                ),
            ],
        ]);

        unset($valide['photo']);

        return $valide;
    }
}
