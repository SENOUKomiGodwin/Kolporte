<x-espace-layout titre="Utilisateurs" actif="utilisateurs">
    <form method="GET" action="{{ route('admin.utilisateurs') }}" class="mb-4">
        <div class="flex gap-2">
            <x-text-input name="q" :value="$recherche" placeholder="Nom, e-mail ou téléphone" class="flex-1 min-h-[48px]" />
            <button type="submit" class="min-h-[48px] px-5 rounded-xl bg-vert text-white font-semibold text-sm">Rechercher</button>
        </div>
    </form>

    @if ($utilisateurs->isEmpty())
        <p class="text-sm text-secondaire bg-white border border-bordure-carte rounded-2xl p-4">Aucun utilisateur trouvé.</p>
    @else
        <ul class="space-y-3">
            @foreach ($utilisateurs as $user)
                <li class="bg-white border border-bordure-carte rounded-2xl p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold truncate">{{ $user->name }}</p>
                            <p class="text-sm text-secondaire truncate">{{ $user->email }}{{ $user->telephone ? ' · '.$user->telephone : '' }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs font-medium text-vert">{{ ['marchand' => 'Marchand', 'promoteur' => 'Promoteur', 'admin' => 'Admin'][$user->role] ?? $user->role }}</p>
                            @if ($user->role === 'promoteur' && $user->portefeuille)
                                <p class="text-xs text-secondaire">{{ number_format((float) $user->portefeuille->solde_disponible, 0, ',', ' ') }} FCFA</p>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-secondaire mt-2">Inscrit le {{ $user->created_at->format('d/m/Y') }}</p>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $utilisateurs->links() }}</div>
    @endif
</x-espace-layout>
