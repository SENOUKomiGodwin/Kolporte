<x-guest-layout>
    <h1 class="font-display text-2xl font-bold mb-2">{{ $article->titre }}</h1>
    <p class="text-sm text-secondaire mb-4">{{ $article->boutique?->nom }}</p>
    @if ($article->photo_url)
        <img src="{{ $article->photo_url }}" alt="{{ $article->titre }}" class="w-full rounded-2xl border border-bordure-carte object-cover mb-4">
    @endif
    <p class="font-display text-xl font-bold text-vert mb-4">{{ number_format((float) $article->prix, 0, ',', ' ') }} FCFA</p>
    <p class="text-sm text-secondaire">Cette page sera disponible prochainement.</p>
</x-guest-layout>
