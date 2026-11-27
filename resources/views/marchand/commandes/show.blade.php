<x-espace-layout titre="Commande n° {{ $commande->id }}" actif="commandes">
    @if (session('statut'))
        <p class="mb-4 px-4 py-3 rounded-xl bg-vert-clair text-vert text-sm font-medium">{{ session('statut') }}</p>
    @endif

    <div class="bg-white border border-bordure-carte rounded-2xl p-4 mb-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs uppercase tracking-wide text-secondaire">Statut</p>
            <p class="text-sm font-semibold {{ ['payee' => 'text-vert', 'livree' => 'text-vert', 'annulee' => 'text-red-600'][$commande->statut] ?? 'text-secondaire' }}">
                {{ ['en_attente_paiement' => 'En attente de paiement', 'payee' => 'Payée — à livrer', 'livree' => 'Livrée', 'annulee' => 'Annulée'][$commande->statut] }}
            </p>
        </div>

        <dl class="text-sm space-y-2">
            <div class="flex justify-between gap-4"><dt class="text-secondaire">Acheteur</dt><dd class="font-medium text-right">{{ $commande->nom_acheteur }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-secondaire">Téléphone</dt><dd class="font-medium">{{ $commande->telephone_acheteur }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-secondaire">Livraison</dt><dd class="font-medium text-right">{{ $commande->adresse_livraison }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-secondaire">Passée via</dt><dd class="font-medium">{{ $commande->lien?->promoteur?->name ?? 'Lien direct' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-secondaire">Date</dt><dd class="font-medium">{{ $commande->created_at->format('d/m/Y H:i') }}</dd></div>
        </dl>
    </div>

    <div class="bg-white border border-bordure-carte rounded-2xl p-4 mb-4">
        <h2 class="font-display font-bold mb-2">Articles</h2>
        @foreach ($commande->lignes as $ligne)
            <div class="flex justify-between py-2 border-b border-bordure-carte last:border-0 text-sm">
                <span>{{ $ligne->article?->titre }} × {{ $ligne->quantite }}</span>
                <span class="font-semibold">{{ number_format((float) $ligne->prix_unitaire * $ligne->quantite, 0, ',', ' ') }} FCFA</span>
            </div>
        @endforeach
        <div class="flex justify-between pt-3 font-display font-bold">
            <span>Total</span>
            <span class="text-vert">{{ number_format((float) $commande->montant_total, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    @if ($commande->transactions->isNotEmpty())
        <div class="bg-white border border-bordure-carte rounded-2xl p-4 mb-4">
            <h2 class="font-display font-bold mb-2">Commissions</h2>
            @foreach ($commande->transactions as $transaction)
                <div class="flex justify-between py-2 text-sm">
                    <span class="text-secondaire">{{ $transaction->type === 'commission' ? 'Commission promoteur' : 'Retrait' }}</span>
                    <span>{{ number_format((float) $transaction->montant, 0, ',', ' ') }} FCFA · {{ ['en_attente' => 'En attente', 'validee' => 'Validée', 'payee' => 'Payée'][$transaction->statut] ?? $transaction->statut }}</span>
                </div>
            @endforeach
        </div>
    @endif

    @if ($commande->statut === 'payee')
        <div class="space-y-3">
            <form method="POST" action="{{ route('marchand.commandes.livrer', $commande) }}">
                @csrf
                <button type="submit" class="w-full min-h-[48px] rounded-xl bg-vert text-white font-semibold">
                    Confirmer la livraison
                </button>
            </form>
            <form method="POST" action="{{ route('marchand.commandes.annuler', $commande) }}"
                  onsubmit="return confirm('Annuler cette commande ? Le stock sera remis et la commission supprimée.');">
                @csrf
                <button type="submit" class="w-full min-h-[48px] rounded-xl border border-red-300 text-red-600 font-semibold">
                    Annuler la commande
                </button>
            </form>
        </div>
    @endif

    <a href="{{ route('marchand.commandes') }}" class="inline-flex min-h-[44px] items-center text-sm text-vert font-medium mt-4">← Retour aux commandes</a>
</x-espace-layout>
