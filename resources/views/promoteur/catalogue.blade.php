<x-espace-layout titre="Catalogue" actif="catalogue">
    <form method="GET" action="{{ route('promoteur') }}" class="mb-5">
        <input type="search" name="q" value="{{ $recherche }}" placeholder="Rechercher un article…"
               class="w-full min-h-[48px] rounded-xl border-bordure-champ px-4 text-encre shadow-sm focus:border-vert focus:ring-vert">
    </form>

    @if ($articles->isEmpty())
        <p class="text-sm text-secondaire bg-white border border-bordure-carte rounded-2xl p-4">
            {{ $recherche !== '' ? 'Aucun article ne correspond à votre recherche.' : 'Aucun article disponible pour le moment.' }}
        </p>
    @else
        <div class="grid grid-cols-2 gap-3">
            @foreach ($articles as $article)
                <a href="{{ route('promoteur.article', $article) }}" class="bg-white border border-bordure-carte rounded-2xl overflow-hidden flex flex-col">
                    @if ($article->photo_url)
                        <img src="{{ $article->photo_url }}" alt="{{ $article->titre }}" class="w-full aspect-square object-cover">
                    @else
                        <div class="w-full aspect-square bg-vert-clair"></div>
                    @endif
                    <div class="p-3 flex flex-col gap-1 flex-1">
                        <p class="text-xs text-secondaire truncate">{{ $article->boutique?->nom }}</p>
                        <p class="text-sm font-semibold leading-tight line-clamp-2">{{ $article->titre }}</p>
                        <p class="text-sm font-bold text-vert mt-auto">{{ number_format((float) $article->prix, 0, ',', ' ') }} FCFA</p>
                        <p class="text-xs text-secondaire">+{{ number_format($article->calculerCommission(), 0, ',', ' ') }} FCFA / vente</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-5">{{ $articles->links() }}</div>
    @endif
</x-espace-layout>
