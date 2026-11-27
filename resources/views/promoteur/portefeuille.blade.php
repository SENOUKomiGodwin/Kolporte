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

    @if (session('statut'))
        <p class="mb-4 px-4 py-3 rounded-xl bg-vert-clair text-vert text-sm font-medium">{{ session('statut') }}</p>
    @endif

    <h2 class="font-display text-lg font-bold mb-3">Retrait</h2>
    @if ($demandeEnAttente)
        <p class="text-sm text-secondaire bg-white border border-bordure-carte rounded-2xl p-4 mb-6">
            Un retrait est déjà en cours de traitement. Vous pourrez en demander un autre une fois qu'il sera traité.
        </p>
    @else
        <form method="POST" action="{{ route('promoteur.portefeuille.retrait') }}" class="bg-white border border-bordure-carte rounded-2xl p-4 mb-6 space-y-3" x-data="{ ouvert: false }">
            @csrf
            <button type="button" @click="ouvert = !ouvert" class="w-full min-h-[48px] rounded-xl bg-vert text-white font-semibold">
                Demander un retrait
            </button>
            <div x-show="ouvert" x-cloak class="space-y-3 pt-2">
                <div>
                    <x-input-label for="montant" :value="'Montant (min. '.number_format($retraitMinimum, 0, ',', ' ').' FCFA)'" />
                    <x-text-input id="montant" name="montant" type="number" min="{{ $retraitMinimum }}" step="1" required class="block mt-1 w-full min-h-[48px]" />
                    <x-input-error :messages="$errors->get('montant')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="moyen_paiement" value="Moyen de paiement" />
                    <select id="moyen_paiement" name="moyen_paiement" required class="block mt-1 w-full min-h-[48px] rounded-xl border-bordure-champ text-encre shadow-sm focus:border-vert focus:ring-vert">
                        <option value="T-Money">T-Money</option>
                        <option value="Flooz">Flooz</option>
                    </select>
                    <x-input-error :messages="$errors->get('moyen_paiement')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="numero_compte" value="Numéro de compte" />
                    <x-text-input id="numero_compte" name="numero_compte" type="tel" required class="block mt-1 w-full min-h-[48px]" />
                    <x-input-error :messages="$errors->get('numero_compte')" class="mt-1" />
                </div>
                <button type="submit" class="w-full min-h-[48px] rounded-xl bg-vert text-white font-semibold">
                    Confirmer la demande
                </button>
            </div>
        </form>
    @endif

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
