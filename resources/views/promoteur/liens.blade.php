<x-espace-layout titre="Mes liens" actif="liens">
    @if ($liens->isEmpty())
        <p class="text-sm text-secondaire bg-white border border-bordure-carte rounded-2xl p-4">
            Vous n'avez pas encore de lien. Parcourez le <a href="{{ route('promoteur') }}" class="text-vert font-semibold hover:underline">catalogue</a> pour en générer un.
        </p>
    @else
        <ul class="space-y-3">
            @foreach ($liens as $lien)
                @php $url = route('article.public', ['article' => $lien->article_id, 'ref' => $lien->code]); @endphp
                <li class="bg-white border border-bordure-carte rounded-2xl p-4">
                    <div class="flex gap-3">
                        @if ($lien->article?->photo_url)
                            <img src="{{ $lien->article->photo_url }}" alt="{{ $lien->article->titre }}" class="w-16 h-16 rounded-xl object-cover border border-bordure-carte shrink-0">
                        @else
                            <div class="w-16 h-16 rounded-xl bg-vert-clair shrink-0"></div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold truncate">{{ $lien->article?->titre }}</p>
                            <p class="text-xs text-secondaire truncate">{{ $lien->article?->boutique?->nom }}</p>
                            <p class="text-xs text-vert break-all mt-1">{{ $url }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-bordure-carte text-center">
                        <div>
                            <p class="font-display font-bold">{{ $lien->nombreClics() }}</p>
                            <p class="text-xs text-secondaire">Clics</p>
                        </div>
                        <div>
                            <p class="font-display font-bold">{{ $lien->nombreVentes() }}</p>
                            <p class="text-xs text-secondaire">Ventes</p>
                        </div>
                        <div>
                            <p class="font-display font-bold text-vert">{{ number_format($lien->gains(), 0, ',', ' ') }}</p>
                            <p class="text-xs text-secondaire">Gains FCFA</p>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-espace-layout>
