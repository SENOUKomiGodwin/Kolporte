<x-espace-layout titre="Commandes" actif="commandes">
    @if (session('statut'))
        <p class="mb-4 px-4 py-3 rounded-xl bg-vert-clair text-vert text-sm font-medium">{{ session('statut') }}</p>
    @endif

    <div class="flex gap-2 overflow-x-auto pb-3 mb-4 -mx-4 px-4">
        <a href="{{ route('marchand.commandes') }}" class="shrink-0 min-h-[40px] px-4 rounded-full text-sm font-medium inline-flex items-center {{ ! $statut ? 'bg-vert text-white' : 'bg-white border border-bordure-carte text-secondaire' }}">Toutes</a>
        @foreach ($statuts as $s)
            <a href="{{ route('marchand.commandes', ['statut' => $s]) }}" class="shrink-0 min-h-[40px] px-4 rounded-full text-sm font-medium inline-flex items-center {{ $statut === $s ? 'bg-vert text-white' : 'bg-white border border-bordure-carte text-secondaire' }}">
                {{ ['en_attente_paiement' => 'En attente', 'payee' => 'Payées', 'livree' => 'Livrées', 'annulee' => 'Annulées'][$s] }}
            </a>
        @endforeach
    </div>

    @if ($commandes->isEmpty())
        <p class="text-sm text-secondaire bg-white border border-bordure-carte rounded-2xl p-4">Aucune commande pour le moment.</p>
    @else
        <ul class="space-y-3">
            @foreach ($commandes as $commande)
                <li>
                    <a href="{{ route('marchand.commandes.show', $commande) }}" class="block bg-white border border-bordure-carte rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold truncate">{{ $commande->nom_acheteur }}</p>
                                <p class="text-sm text-secondaire truncate">
                                    {{ $commande->lignes->map(fn ($l) => $l->article?->titre)->filter()->implode(', ') }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-display font-bold">{{ number_format((float) $commande->montant_total, 0, ',', ' ') }} FCFA</p>
                                <p class="text-xs {{ ['payee' => 'text-vert', 'livree' => 'text-vert', 'annulee' => 'text-red-600'][$commande->statut] ?? 'text-secondaire' }}">
                                    {{ ['en_attente_paiement' => 'En attente de paiement', 'payee' => 'Payée', 'livree' => 'Livrée', 'annulee' => 'Annulée'][$commande->statut] }}
                                </p>
                            </div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $commandes->links() }}</div>
    @endif
</x-espace-layout>
