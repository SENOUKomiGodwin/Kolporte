<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\SuiviLien;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArticlePublicController extends Controller
{
    public function __invoke(Request $request, Article $article, SuiviLien $suivi): Response
    {
        $article->load('boutique');

        $lien = null;
        $code = $request->query('ref');

        if (is_string($code) && $code !== '' && ! $suivi->estRobot($request)) {
            $lien = $suivi->lienDepuisCode($code, $article);

            if ($lien) {
                $suivi->enregistrerClic($lien, $suivi->ip($request));
            }
        }

        // Encadré promoteur : lien valide de cette requête ou cookie actif pour cet article
        $lienCookie = $suivi->lienDepuisCookie($request);
        $lienActif = $lien ?? ($lienCookie?->article_id === $article->id ? $lienCookie : null);

        $reponse = response()->view('article.public', [
            'article' => $article,
            'lienActif' => $lienActif,
        ]);

        // Dernier clic valide gagne : un nouveau code remplace l'ancien cookie
        if ($lien) {
            $reponse->cookie($suivi->cookieRef($lien));
        }

        return $reponse;
    }
}
