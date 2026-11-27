<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.pwa')
        <title>Paiement échoué · Kolporte</title>
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
                <div class="flex items-center px-4 h-14">
                    <span class="font-display text-xl font-bold text-vert">Kolporte</span>
                </div>
            </header>

            <main class="flex-1 px-4 py-6">
                <div class="bg-white border border-bordure-carte rounded-2xl p-6 text-center">
                    <p class="font-display text-lg font-bold text-encre mb-2">Paiement échoué</p>
                    @if (session('motif') === 'stock')
                        <p class="text-sm text-secondaire mb-4">Le stock de cet article est insuffisant : votre commande a été annulée. Aucun montant n'a été débité.</p>
                    @else
                        <p class="text-sm text-secondaire mb-4">Votre paiement n'a pas abouti et la commande n° {{ $commande->id }} a été annulée. Aucun montant n'a été débité.</p>
                    @endif
                    <a href="{{ $commande->lignes->first()?->article ? route('article.public', $commande->lignes->first()->article) : '/' }}" class="inline-flex min-h-[48px] items-center rounded-xl bg-vert text-white font-semibold px-6">
                        Retour à l'article
                    </a>
                </div>
            </main>
        </div>
    </body>
</html>
