<x-espace-layout titre="Retraits" actif="retraits">
    @if (session('statut'))
        <p class="mb-4 px-4 py-3 rounded-xl bg-vert-clair text-vert text-sm font-medium">{{ session('statut') }}</p>
    @endif

    <div class="bg-white border border-bordure-carte rounded-2xl p-4 mb-4">
        <p class="font-display text-2xl font-bold {{ $enAttente > 0 ? 'text-ambre' : 'text-vert' }}">{{ $enAttente }} demande{{ $enAttente > 1 ? 's' : '' }} en attente</p>
    </div>

    <div class="flex gap-2 overflow-x-auto pb-3 mb-4 -mx-4 px-4">
        <a href="{{ route('admin.retraits') }}" class="shrink-0 min-h-[40px] px-4 rounded-full text-sm font-medium inline-flex items-center {{ ! $statut ? 'bg-vert text-white' : 'bg-white border border-bordure-carte text-secondaire' }}">Toutes</a>
        @foreach ($statuts as $s)
            <a href="{{ route('admin.retraits', ['statut' => $s]) }}" class="shrink-0 min-h-[40px] px-4 rounded-full text-sm font-medium inline-flex items-center {{ $statut === $s ? 'bg-vert text-white' : 'bg-white border border-bordure-carte text-secondaire' }}">
                {{ ['en_attente' => 'En attente', 'validee' => 'Validées', 'refusee' => 'Refusées'][$s] }}
            </a>
        @endforeach
    </div>

    @if ($demandes->isEmpty())
        <p class="text-sm text-secondaire bg-white border border-bordure-carte rounded-2xl p-4">Aucune demande de retrait.</p>
    @else
        <ul class="space-y-3">
            @foreach ($demandes as $demande)
                <li class="bg-white border border-bordure-carte rounded-2xl p-4">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div>
                            <p class="font-semibold">{{ $demande->promoteur?->name ?? 'Promoteur' }}</p>
                            <p class="text-sm text-secondaire">
                                {{ $demande->moyen_paiement }} · •••• {{ substr($demande->numero_compte, -4) }}
                            </p>
                            <p class="text-xs text-secondaire mt-1">{{ $demande->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <p class="font-display font-bold text-vert shrink-0">{{ number_format((float) $demande->montant, 0, ',', ' ') }} FCFA</p>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-bordure-carte">
                        <span class="text-xs font-medium {{ ['en_attente' => 'text-ambre', 'validee' => 'text-vert', 'refusee' => 'text-red-600'][$demande->statut] }}">
                            {{ ['en_attente' => 'En attente', 'validee' => 'Validée', 'refusee' => 'Refusée'][$demande->statut] }}
                            @if ($demande->validee_par && $demande->validateur)
                                par {{ $demande->validateur->name }}
                            @endif
                        </span>
                        @if ($demande->statut === 'en_attente')
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.retraits.refuser', $demande) }}">
                                    @csrf
                                    <button type="submit" class="min-h-[44px] px-4 rounded-xl border border-red-300 text-red-600 text-sm font-semibold">Refuser</button>
                                </form>
                                <form method="POST" action="{{ route('admin.retraits.valider', $demande) }}">
                                    @csrf
                                    <button type="submit" class="min-h-[44px] px-4 rounded-xl bg-vert text-white text-sm font-semibold">Valider</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $demandes->links() }}</div>
    @endif
</x-espace-layout>
