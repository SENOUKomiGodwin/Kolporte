<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @include('partials.pwa')
        <title>{{ $article->titre }} · Kolporte</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..700&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700&display=swap" rel="stylesheet">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="font-sans bg-fond text-encre antialiased">
        <div class="min-h-screen flex flex-col max-w-md mx-auto">
            <header class="sticky top-0 z-10 bg-fond/95 backdrop-blur border-b border-bordure-carte">
                <div class="flex items-center justify-between px-4 h-14">
                    <span class="font-display text-xl font-bold text-vert">Kolporte</span>
                    <span class="text-sm text-secondaire">{{ $article->boutique?->nom }}</span>
                </div>
            </header>

            <main class="flex-1 px-4 py-6">
                @if ($article->photo_url)
                    <img src="{{ $article->photo_url }}" alt="{{ $article->titre }}" class="w-full rounded-2xl border border-bordure-carte object-cover mb-4">
                @endif

                <h1 class="font-display text-2xl font-bold mb-1">{{ $article->titre }}</h1>
                <p class="text-lg font-display font-bold text-vert mb-4">{{ number_format((float) $article->prix, 0, ',', ' ') }} FCFA</p>

                @if ($article->description)
                    <p class="text-sm text-encre whitespace-pre-line mb-4">{{ $article->description }}</p>
                @endif

                @if ($lienActif)
                    <div class="bg-vert-clair border border-vert/20 rounded-2xl px-4 py-3 mb-4">
                        <p class="text-sm text-vert">Vous passez par le lien d'un promoteur : le prix reste le même pour vous.</p>
                    </div>
                @endif

                @if ($article->stock <= 0)
                    <div class="bg-white border border-bordure-carte rounded-2xl p-6 text-center">
                        <p class="font-display text-lg font-bold text-secondaire">Article épuisé</p>
                    </div>
                @else
                    <div id="zone-commande" class="bg-white border border-bordure-carte rounded-2xl p-4">
                        <h2 class="font-display font-bold mb-3">Commander</h2>
                        <p class="text-xs text-secondaire mb-3">Paiement par Mobile Money — aucun compte requis.</p>
                        <form method="POST" action="{{ route('commande.store', $article) }}" class="space-y-3">
                            @csrf
                            <div>
                                <x-input-label for="nom" value="Nom complet" />
                                <x-text-input id="nom" name="nom" :value="old('nom')" required autocomplete="name" class="block mt-1 w-full min-h-[48px]" />
                                <x-input-error :messages="$errors->get('nom')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="telephone" value="Téléphone" />
                                <x-text-input id="telephone" name="telephone" type="tel" :value="old('telephone')" required autocomplete="tel" class="block mt-1 w-full min-h-[48px]" />
                                <x-input-error :messages="$errors->get('telephone')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="adresse_livraison" value="Adresse de livraison" />
                                <textarea id="adresse_livraison" name="adresse_livraison" rows="2" required class="block mt-1 w-full rounded-xl border-bordure-champ text-encre shadow-sm focus:border-vert focus:ring-vert">{{ old('adresse_livraison') }}</textarea>
                                <x-input-error :messages="$errors->get('adresse_livraison')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="quantite" :value="'Quantité (stock : '.$article->stock.')'" />
                                <x-text-input id="quantite" name="quantite" type="number" min="1" max="{{ $article->stock }}" value="{{ old('quantite', 1) }}" required class="block mt-1 w-full min-h-[48px]" />
                                <x-input-error :messages="$errors->get('quantite')" class="mt-1" />
                            </div>
                            <button type="submit" class="w-full min-h-[48px] rounded-xl bg-vert text-white font-semibold">
                                Continuer vers le paiement
                            </button>
                        </form>
                    </div>
                @endif
            </main>
        </div>
    </body>
</html>
