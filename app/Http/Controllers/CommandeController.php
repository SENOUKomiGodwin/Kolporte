<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Commande;
use App\Models\LienPromoteur;
use App\Services\ConfirmerPaiement;
use App\Services\Paiement\PaiementService;
use App\Services\SuiviLien;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommandeController extends Controller
{
    public function store(
        Request $request,
        Article $article,
        SuiviLien $suivi,
        PaiementService $paiement,
    ): RedirectResponse {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:30'],
            'adresse_livraison' => ['required', 'string', 'max:500'],
            'quantite' => ['required', 'integer', 'min:1', 'max:'.$article->stock],
        ]);

        // Le cookie « ref » ne compte que pour la commande de l'article du lien
        $lienCookie = $suivi->lienDepuisCookie($request);
        $lien = $lienCookie && (int) $lienCookie->article_id === (int) $article->id ? $lienCookie : null;

        // Anti-auto-commission : même utilisateur connecté ou même téléphone
        $commission = 0.0;
        if ($lien && ! $this->estLePromoteur($request, $lien)) {
            $commission = round($article->calculerCommission() * $donnees['quantite'], 2);
        }

        $commande = DB::transaction(function () use ($article, $lien, $donnees, $commission) {
            $c = Commande::create([
                'lien_id' => $lien?->id,
                'nom_acheteur' => $donnees['nom'],
                'telephone_acheteur' => $donnees['telephone'],
                'adresse_livraison' => $donnees['adresse_livraison'],
                'montant_total' => (float) $article->prix * $donnees['quantite'],
                'statut' => 'en_attente_paiement',
            ]);

            $c->lignes()->create([
                'article_id' => $article->id,
                'quantite' => $donnees['quantite'],
                'prix_unitaire' => $article->prix,
                'commission' => $commission,
            ]);

            return $c;
        });

        return redirect($paiement->initier($commande));
    }

    /** Page de paiement simulé (récap + Payer/Échouer). */
    public function paiement(Commande $commande): View|RedirectResponse
    {
        $commande->load('lignes.article.boutique');

        return match ($commande->statut) {
            'payee', 'livree' => redirect()->route('commande.confirmation', $commande),
            'annulee' => redirect()->route('commande.echec', $commande),
            default => view('commande.paiement', ['commande' => $commande]),
        };
    }

    /** Bouton « Payer » du paiement simulé. */
    public function payer(Commande $commande, ConfirmerPaiement $confirmer): RedirectResponse
    {
        $resultat = $confirmer->confirmer($commande);

        return match ($resultat) {
            'payee', 'deja_payee' => redirect()->route('commande.confirmation', $commande),
            default => redirect()->route('commande.echec', $commande)->with('motif', 'stock'),
        };
    }

    /** Bouton « Échouer » du paiement simulé : la commande est annulée. */
    public function echecPaiement(Commande $commande): RedirectResponse
    {
        if ($commande->statut === 'en_attente_paiement') {
            $commande->update(['statut' => 'annulee']);
        }

        return redirect()->route('commande.echec', $commande);
    }

    /** Page d'échec (affichée aussi pour stock insuffisant). */
    public function echec(Commande $commande): View|RedirectResponse
    {
        if ($commande->statut === 'en_attente_paiement') {
            return redirect()->route('commande.paiement', $commande);
        }

        return view('commande.echec', ['commande' => $commande]);
    }

    /** Page de confirmation : récapitulatif + numéro de commande. */
    public function confirmation(Commande $commande): View|RedirectResponse
    {
        if (! in_array($commande->statut, ['payee', 'livree'], true)) {
            return redirect()->route('commande.paiement', $commande);
        }

        return view('commande.confirmation', [
            'commande' => $commande->load('lignes.article.boutique', 'lien.promoteur'),
        ]);
    }

    /** Retour de FedaPay après paiement Mobile Money. */
    public function fedapayRetour(Request $request): RedirectResponse
    {
        // La référence métier est dans ?ref=KP-… ; le webhook reste la source de vérité.
        $reference = $request->query('ref');
        $commande = is_string($reference)
            ? Commande::where('reference_paiement', $reference)->first()
            : null;

        if ($commande && in_array($commande->statut, ['payee', 'livree'], true)) {
            return redirect()->route('commande.confirmation', $commande);
        }

        if ($commande) {
            return redirect()->route('commande.paiement', $commande)
                ->with('info', 'Votre paiement est en cours de confirmation.');
        }

        return redirect('/');
    }

    private function estLePromoteur(Request $request, LienPromoteur $lien): bool
    {
        $user = $request->user();
        if ($user && $user->id === $lien->promoteur_id) {
            return true;
        }

        $normaliser = fn (string $tel) => preg_replace('/\D+/', '', $tel);
        $telPromoteur = (string) ($lien->promoteur?->telephone ?? '');
        $telAcheteur = (string) $request->input('telephone', '');

        return $telPromoteur !== ''
            && $normaliser($telPromoteur) !== ''
            && $normaliser($telPromoteur) === $normaliser($telAcheteur);
    }
}
