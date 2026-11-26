<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Clic;
use App\Models\LienPromoteur;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

/** Suivi des liens de partage : clics et cookie « ref » (attribution, 7 jours). */
class SuiviLien
{
    public const COOKIE = 'ref';

    private const DELAI_CLIC_HEURES = 24;

    /** Robots et prévisualisations de liens : jamais de clic. */
    public function estRobot(Request $request): bool
    {
        $agent = (string) $request->userAgent();

        return $agent !== '' && preg_match('/bot|crawler|spider|facebookexternalhit|whatsapp/i', $agent) === 1;
    }

    /** Adresse IP réelle du visiteur (derrière le proxy de Render). */
    public function ip(Request $request): string
    {
        return (string) $request->ip();
    }

    /**
     * Lien correspondant au code saisi, uniquement s'il appartient à cet article.
     * Code inconnu ou d'un autre article : null, silencieusement.
     */
    public function lienDepuisCode(string $code, Article $article): ?LienPromoteur
    {
        return LienPromoteur::query()
            ->where('code', $code)
            ->where('article_id', $article->id)
            ->first();
    }

    /**
     * Enregistre un clic, sauf si la même IP a déjà cliqué ce lien
     * dans les dernières 24 h (anti-fraude).
     */
    public function enregistrerClic(LienPromoteur $lien, string $ip): bool
    {
        $dejaCompte = Clic::query()
            ->where('lien_id', $lien->id)
            ->where('adresse_ip', $ip)
            ->where('created_at', '>=', now()->subHours(self::DELAI_CLIC_HEURES))
            ->exists();

        if ($dejaCompte) {
            return false;
        }

        Clic::create([
            'lien_id' => $lien->id,
            'adresse_ip' => $ip,
        ]);

        return true;
    }

    /**
     * Lit le lien actif depuis le cookie « ref » (étape 5 : attribution commande).
     * Le cookie ne compte que pour la commande de l'article du lien.
     */
    public function lienDepuisCookie(Request $request): ?LienPromoteur
    {
        $code = $request->cookie(self::COOKIE);

        if (! is_string($code) || $code === '') {
            return null;
        }

        $lien = LienPromoteur::query()->where('code', $code)->with('article')->first();

        return $lien?->article ? $lien : null;
    }

    /** Cookie « ref » : HttpOnly, SameSite Lax, Secure en production. */
    public function cookieRef(LienPromoteur $lien): Cookie
    {
        return cookie(
            self::COOKIE,
            $lien->code,
            (int) config('kolporte.validite_lien_jours') * 24 * 60,
            httpOnly: true,
            sameSite: 'lax',
            secure: app()->isProduction(),
        );
    }
}
