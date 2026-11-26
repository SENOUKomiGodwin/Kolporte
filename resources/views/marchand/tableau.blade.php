<x-espace-layout titre="Tableau de bord" actif="tableau">
    @if (session('statut'))
        <p class="mb-4 px-4 py-3 rounded-xl bg-vert-clair text-vert text-sm font-medium">{{ session('statut') }}</p>
    @endif

    @if (! $boutique)
        <div class="bg-white border border-bordure-carte rounded-2xl p-6 text-center">
            <h2 class="font-display text-xl font-bold mb-2">Créez votre boutique</h2>
            <p class="text-sm text-secondaire mb-6">Pour publier des articles et suivre vos ventes, commencez par créer votre boutique.</p>
            <a href="{{ route('marchand.boutique.edit') }}" class="inline-flex items-center justify-center min-h-[48px] px-6 rounded-xl bg-vert text-white font-semibold">
                Créer ma boutique
            </a>
        </div>
    @else
        <div class="mb-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-secondaire">{{ $boutique->nom }}</p>
                <a href="{{ route('marchand.boutique.edit') }}" class="text-sm text-vert hover:underline">Modifier la boutique</a>
            </div>
            @if ($boutique->logo_url)
                <img src="{{ $boutique->logo_url }}" alt="Logo {{ $boutique->nom }}" class="w-14 h-14 rounded-xl object-cover border border-bordure-carte">
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="bg-white border border-bordure-carte rounded-2xl p-4">
                <p class="text-xs text-secondaire mb-1">Ventes du mois</p>
                <p class="font-display text-xl font-bold text-vert">{{ number_format($stats['ventes_mois'], 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="bg-white border border-bordure-carte rounded-2xl p-4">
                <p class="text-xs text-secondaire mb-1">Commandes du mois</p>
                <p class="font-display text-xl font-bold">{{ $stats['commandes_mois'] }}</p>
            </div>
            <div class="bg-white border border-bordure-carte rounded-2xl p-4">
                <p class="text-xs text-secondaire mb-1">Commissions versées</p>
                <p class="font-display text-xl font-bold">{{ number_format($stats['commissions_versees'], 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="bg-white border border-bordure-carte rounded-2xl p-4">
                <p class="text-xs text-secondaire mb-1">Promoteurs actifs</p>
                <p class="font-display text-xl font-bold">{{ $stats['promoteurs_actifs'] }}</p>
            </div>
        </div>

        <h2 class="font-display text-lg font-bold mb-3">Commandes à valider</h2>
        @if ($stats['commandes_a_valider']->isEmpty())
            <p class="text-sm text-secondaire bg-white border border-bordure-carte rounded-2xl p-4 mb-6">Aucune commande en attente de validation.</p>
        @else
            <ul class="space-y-3 mb-6">
                @foreach ($stats['commandes_a_valider'] as $commande)
                    <li class="bg-white border border-bordure-carte rounded-2xl p-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ $commande->nom_acheteur }}</p>
                            <p class="text-sm text-secondaire">
                                {{ number_format((float) $commande->montant_total, 0, ',', ' ') }} FCFA
                                @if ($commande->lien?->promoteur)
                                    · via {{ $commande->lien->promoteur->name }}
                                @endif
                            </p>
                        </div>
                        <button type="button" disabled class="min-h-[44px] px-4 rounded-xl bg-vert-clair text-vert text-sm font-medium opacity-60 cursor-not-allowed">
                            Valider la livraison
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif

        <h2 class="font-display text-lg font-bold mb-3">Meilleurs promoteurs</h2>
        @if ($stats['meilleurs_promoteurs']->isEmpty())
            <p class="text-sm text-secondaire bg-white border border-bordure-carte rounded-2xl p-4">Aucune vente attribuée pour le moment.</p>
        @else
            <ul class="space-y-2">
                @foreach ($stats['meilleurs_promoteurs'] as $lien)
                    <li class="bg-white border border-bordure-carte rounded-2xl px-4 py-3 flex items-center justify-between">
                        <span class="font-medium">{{ $lien->promoteur?->name ?? 'Promoteur' }}</span>
                        <span class="text-sm text-secondaire">{{ $lien->ventes }} vente{{ $lien->ventes > 1 ? 's' : '' }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</x-espace-layout>
