<x-espace-layout titre="Articles" actif="articles">
    @if (session('statut'))
        <p class="mb-4 px-4 py-3 rounded-xl bg-vert-clair text-vert text-sm font-medium">{{ session('statut') }}</p>
    @endif

    @if (! $boutique)
        <div class="bg-white border border-bordure-carte rounded-2xl p-6 text-center">
            <h2 class="font-display text-xl font-bold mb-2">Créez d'abord votre boutique</h2>
            <p class="text-sm text-secondaire mb-6">Les articles sont rattachés à une boutique.</p>
            <a href="{{ route('marchand.boutique.edit') }}" class="inline-flex items-center justify-center min-h-[48px] px-6 rounded-xl bg-vert text-white font-semibold">
                Créer ma boutique
            </a>
        </div>
    @else
        <a href="{{ route('marchand.articles.create') }}" class="mb-4 flex items-center justify-center min-h-[48px] rounded-xl bg-vert text-white font-semibold">
            Nouvel article
        </a>

        @if ($articles->isEmpty())
            <p class="text-sm text-secondaire bg-white border border-bordure-carte rounded-2xl p-4">Aucun article pour le moment.</p>
        @else
            <ul class="space-y-3">
                @foreach ($articles as $article)
                    <li class="bg-white border border-bordure-carte rounded-2xl p-4 flex gap-4">
                        @if ($article->photo_url)
                            <img src="{{ $article->photo_url }}" alt="{{ $article->titre }}" class="w-20 h-20 rounded-xl object-cover border border-bordure-carte shrink-0">
                        @else
                            <div class="w-20 h-20 rounded-xl bg-vert-clair shrink-0"></div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold truncate">{{ $article->titre }}</p>
                            <p class="text-sm text-secondaire">
                                {{ number_format((float) $article->prix, 0, ',', ' ') }} FCFA · stock {{ $article->stock }}
                            </p>
                            <p class="text-sm text-vert">
                                Commission : {{ $article->type_commission === 'pourcentage' ? rtrim(rtrim(number_format((float) $article->valeur_commission, 2, ',', ' '), '0'), ',').' %' : number_format((float) $article->valeur_commission, 0, ',', ' ').' FCFA' }}
                                · gain {{ number_format($article->calculerCommission(), 0, ',', ' ') }} FCFA/vente
                            </p>
                        </div>
                        <div class="flex flex-col gap-2 shrink-0">
                            <a href="{{ route('marchand.articles.edit', $article) }}" class="flex items-center justify-center min-h-[44px] px-3 rounded-xl bg-vert-clair text-vert text-sm font-semibold">Modifier</a>
                            <form method="POST" action="{{ route('marchand.articles.destroy', $article) }}" onsubmit="return confirm('Supprimer cet article ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="min-h-[44px] px-3 rounded-xl border border-bordure-champ text-secondaire text-sm font-medium">Supprimer</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</x-espace-layout>
