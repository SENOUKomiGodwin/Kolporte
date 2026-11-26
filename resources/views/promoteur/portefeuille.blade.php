<x-espace-layout titre="Portefeuille" actif="portefeuille">
    <div class="bg-white border border-bordure-carte rounded-2xl p-5 mb-4 text-center">
        <p class="text-xs uppercase tracking-wide text-secondaire mb-1">Solde disponible</p>
        <p class="font-display text-3xl font-bold text-vert">{{ number_format((float) ($portefeuille->solde_disponible ?? 0), 0, ',', ' ') }} FCFA</p>
    </div>

    <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="bg-white border border-bordure-carte rounded-2xl p-4">
            <p class="text-xs text-secondaire mb-1">En attente</p>
            <p class="font-display text-xl font-bold">{{ number_format((float) ($portefeuille->solde_en_attente ?? 0), 0, ',', ' ') }} FCFA</p>
        </div>
        <div class="bg-white border border-bordure-carte rounded-2xl p-4">
            <p class="text-xs text-secondaire mb-1">Déjà payé</p>
            <p class="font-display text-xl font-bold">{{ number_format($dejaPaye, 0, ',', ' ') }} FCFA</p>
        </div>
    </div>

    <button type="button" disabled class="w-full min-h-[48px] rounded-xl bg-vert-clair text-vert font-semibold opacity-60 cursor-not-allowed mb-6">
        Demander un retrait
    </button>

    <h2 class="font-display text-lg font-bold mb-3">Historique</h2>
    @if ($transactions->isEmpty())
        <p class="text-sm text-secondaire bg-white border border-bordure-carte rounded-2xl p-4">Aucune transaction pour le moment.</p>
    @else
        <ul class="space-y-3">
            @foreach ($transactions as $transaction)
                @php
                    $estRetrait = $transaction->type === 'retrait';
                    $article = $transaction->commande?->lignes->first()?->article;
                    $libelle = $estRetrait ? 'Retrait vers T-Money' : ($article?->titre ?? 'Commission');
                    $signe = $estRetrait ? '−' : '+';
                    $montant = number_format((float) $transaction->montant, 0, ',', ' ');
                @endphp
                <li class="bg-white border border-bordure-carte rounded-2xl p-4 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold truncate">{{ $libelle }}</p>
                        <p class="text-xs text-secondaire">
                            {{ $estRetrait ? 'Retrait' : 'Commission' }} · {{ $transaction->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="font-display font-bold {{ $estRetrait ? 'text-red-600' : 'text-vert' }}">{{ $signe }}{{ $montant }} FCFA</p>
                        <p class="text-xs {{ ['en_attente' => 'text-amber-600', 'validee' => 'text-vert', 'payee' => 'text-secondaire'][$transaction->statut] ?? 'text-secondaire' }}">
                            {{ ['en_attente' => 'En attente', 'validee' => 'Validée', 'payee' => 'Payée'][$transaction->statut] ?? $transaction->statut }}
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $transactions->links() }}</div>
    @endif
</x-espace-layout>
