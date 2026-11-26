<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Paiement · Kolporte</title>
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
                @if (session('info'))
                    <div class="bg-vert-clair border border-vert/20 rounded-2xl px-4 py-3 mb-4">
                        <p class="text-sm text-vert">{{ session('info') }}</p>
                    </div>
                @endif

                <div class="bg-white border border-bordure-carte rounded-2xl p-4 mb-4">
                    <h1 class="font-display text-xl font-bold mb-3">Récapitulatif</h1>
                    <p class="text-xs uppercase tracking-wide text-secondaire mb-2">Commande n° {{ $commande->id }}</p>
                    @foreach ($commande->lignes as $ligne)
                        <div class="flex justify-between py-2 border-b border-bordure-carte last:border-0 text-sm">
                            <span>{{ $ligne->article?->titre }} × {{ $ligne->quantite }}</span>
                            <span class="font-semibold">{{ number_format((float) $ligne->prix_unitaire * $ligne->quantite, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between pt-3 font-display font-bold text-lg">
                        <span>Total</span>
                        <span class="text-vert">{{ number_format((float) $commande->montant_total, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>

                <div class="bg-white border border-bordure-carte rounded-2xl p-4">
                    <h2 class="font-display font-bold mb-1">Paiement Mobile Money</h2>
                    <p class="text-sm text-secondaire mb-4">Mode de paiement simulé pour la démonstration.</p>
                    <div class="space-y-3">
                        <form method="POST" action="{{ route('commande.paiement.confirmer', $commande) }}">
                            @csrf
                            <button type="submit" class="w-full min-h-[48px] rounded-xl bg-vert text-white font-semibold">
                                Payer {{ number_format((float) $commande->montant_total, 0, ',', ' ') }} FCFA
                            </button>
                        </form>
                        <form method="POST" action="{{ route('commande.paiement.echec', $commande) }}">
                            @csrf
                            <button type="submit" class="w-full min-h-[48px] rounded-xl border border-bordure-champ text-secondaire font-semibold">
                                Échouer le paiement
                            </button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
