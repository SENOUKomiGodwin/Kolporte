<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemandeRetrait;
use App\Services\RefuserRetrait;
use App\Services\ValiderRetrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RetraitController extends Controller
{
    private const STATUTS = ['en_attente', 'validee', 'refusee'];

    public function index(Request $request): View
    {
        $statut = $request->query('statut');

        $demandes = DemandeRetrait::query()
            ->with('portefeuille.utilisateur', 'transaction')
            ->when(in_array($statut, self::STATUTS, true), fn ($q) => $q->where('statut', $statut))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.retraits', [
            'demandes' => $demandes,
            'statut' => $statut,
            'statuts' => self::STATUTS,
            'enAttente' => DemandeRetrait::where('statut', 'en_attente')->count(),
        ]);
    }

    public function valider(Request $request, DemandeRetrait $demande, ValiderRetrait $service): RedirectResponse
    {
        $service->valider($demande, $request->user());

        return redirect()->route('admin.retraits')->with('statut', 'Retrait validé.');
    }

    public function refuser(DemandeRetrait $demande, RefuserRetrait $service): RedirectResponse
    {
        $service->refuser($demande);

        return redirect()->route('admin.retraits')->with('statut', 'Retrait refusé : le montant a été recrédité.');
    }
}
