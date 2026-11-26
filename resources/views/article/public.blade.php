<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
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
                    {{-- Emplacement du formulaire de commande (étape 5) --}}
                    <div id="zone-commande" class="bg-white border border-bordure-carte rounded-2xl p-4">
                        <p class="text-sm text-secondaire text-center">Le formulaire de commande arrive prochainement.</p>
                    </div>
                @endif
            </main>
        </div>
    </body>
</html>
