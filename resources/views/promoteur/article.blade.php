<x-espace-layout :titre="$article->titre" actif="catalogue">
    @if (session('statut'))
        <p class="mb-4 px-4 py-3 rounded-xl bg-vert-clair text-vert text-sm font-medium">{{ session('statut') }}</p>
    @endif

    @if ($article->photo_url)
        <img src="{{ $article->photo_url }}" alt="{{ $article->titre }}" class="w-full rounded-2xl border border-bordure-carte object-cover mb-4">
    @endif

    <p class="text-sm text-secondaire mb-1">{{ $article->boutique?->nom }}</p>
    <h2 class="font-display text-2xl font-bold mb-2">{{ $article->titre }}</h2>
    <p class="font-display text-xl font-bold text-vert mb-3">{{ number_format((float) $article->prix, 0, ',', ' ') }} FCFA</p>

    @if ($article->description)
        <p class="text-sm text-encre whitespace-pre-line mb-4">{{ $article->description }}</p>
    @endif

    <div class="bg-vert-clair rounded-2xl px-4 py-3 mb-6">
        <p class="text-sm text-vert">Vous gagnez <span class="font-bold">{{ number_format($article->calculerCommission(), 0, ',', ' ') }} FCFA</span> par vente.</p>
    </div>

    @if (! $lien)
        <form method="POST" action="{{ route('promoteur.article.lien', $article) }}">
            @csrf
            <x-primary-button class="w-full">Générer mon lien</x-primary-button>
        </form>
    @else
        <div class="bg-white border border-bordure-carte rounded-2xl p-4 mb-4">
            <p class="text-xs text-secondaire mb-1">Votre lien de partage</p>
            <p class="text-sm font-medium break-all text-vert">{{ $url }}</p>
        </div>

        <div class="grid grid-cols-3 gap-2 mb-6">
            <button type="button" onclick="copierLien(this)" class="min-h-[48px] rounded-xl bg-vert text-white text-sm font-semibold">Copier le lien</button>
            @if ($article->photo_url)
                <a href="{{ $article->photo_url }}" download="{{ $article->titre }}" class="flex items-center justify-center min-h-[48px] rounded-xl bg-vert-clair text-vert text-sm font-semibold">Photos</a>
            @endif
            <button type="button" onclick="partagerLien(this)" class="min-h-[48px] rounded-xl border border-bordure-champ text-encre text-sm font-semibold">Partager</button>
        </div>

        <h3 class="font-display text-lg font-bold mb-3">Statistiques du lien</h3>
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white border border-bordure-carte rounded-2xl p-4 text-center">
                <p class="font-display text-xl font-bold">{{ $lien->nombreClics() }}</p>
                <p class="text-xs text-secondaire">Clics</p>
            </div>
            <div class="bg-white border border-bordure-carte rounded-2xl p-4 text-center">
                <p class="font-display text-xl font-bold">{{ $lien->nombreVentes() }}</p>
                <p class="text-xs text-secondaire">Ventes</p>
            </div>
            <div class="bg-white border border-bordure-carte rounded-2xl p-4 text-center">
                <p class="font-display text-xl font-bold text-vert">{{ number_format($lien->gains(), 0, ',', ' ') }}</p>
                <p class="text-xs text-secondaire">Gains (FCFA)</p>
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
        const urlPartage = @json($url);
        const titrePartage = @json($article->titre);

        async function copierLien(bouton) {
            try {
                await navigator.clipboard.writeText(urlPartage);
            } catch (e) {
                const zone = document.createElement('textarea');
                zone.value = urlPartage;
                document.body.appendChild(zone);
                zone.select();
                document.execCommand('copy');
                zone.remove();
            }
            bouton.textContent = 'Copié !';
            setTimeout(() => bouton.textContent = 'Copier le lien', 1500);
        }

        async function partagerLien() {
            if (navigator.share) {
                try {
                    await navigator.share({ title: titrePartage, text: titrePartage, url: urlPartage });
                    return;
                } catch (e) {
                    if (e && e.name === 'AbortError') return;
                }
            }
            window.open('https://wa.me/?text=' + encodeURIComponent(titrePartage + ' ' + urlPartage), '_blank');
        }
    </script>
    @endpush
</x-espace-layout>
