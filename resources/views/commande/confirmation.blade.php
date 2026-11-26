<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Commande confirmée · Kolporte</title>
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
                <div class="bg-vert-clair border border-vert/20 rounded-2xl p-6 text-center mb-4">
                    <p class="font-display text-lg font-bold text-vert mb-1">Paiement confirmé</p>
                    <p class="text-sm text-secondaire">Merci pour votre commande.</p>
                </div>

                <div class="bg-white border border-bordure-carte rounded-2xl p-4 mb-4">
                    <p class="text-xs uppercase tracking-wide text-secondaire mb-2">Numéro de commande</p>
                    <p class="font-display text-2xl font-bold text-vert mb-4">n° {{ $commande->id }}</p>
                    @foreach ($commande->lignes as $ligne)
                        <div class="flex justify-between py-2 border-b border-bordure-carte last:border-0 text-sm">
                            <span>{{ $ligne->article?->titre }} × {{ $ligne->quantite }}</span>
                            <span class="font-semibold">{{ number_format((float) $ligne->prix_unitaire * $ligne->quantite, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between pt-3 font-display font-bold text-lg">
                        <span>Total payé</span>
                        <span class="text-vert">{{ number_format((float) $commande->montant_total, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>

                <div class="bg-white border border-bordure-carte rounded-2xl p-4 text-sm text-secondaire">
                    <p><span class="font-semibold text-encre">Livraison :</span> {{ $commande->adresse_livraison }}</p>
                    <p class="mt-1"><span class="font-semibold text-encre">Téléphone :</span> {{ $commande->telephone_acheteur }}</p>
                </div>
            </main>
        </div>
    </body>
</html>
