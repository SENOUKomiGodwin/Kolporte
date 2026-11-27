<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.pwa')
        <meta name="theme-color" content="#0F5C45">
        <title>Hors ligne · Kolporte</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..700&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700&display=swap" rel="stylesheet">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="font-sans bg-fond text-encre antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center px-6 text-center max-w-md mx-auto">
            <span class="font-display text-2xl font-bold text-vert mb-8">Kolporte</span>
            <div class="bg-white border border-bordure-carte rounded-2xl p-8 w-full">
                <p class="font-display text-xl font-bold mb-2">Vous êtes hors ligne</p>
                <p class="text-sm text-secondaire mb-6">Kolporte a besoin d'une connexion Internet. Vérifiez votre réseau puis réessayez.</p>
                <button type="button" onclick="location.reload()" class="w-full min-h-[48px] rounded-xl bg-vert text-white font-semibold">
                    Réessayer
                </button>
            </div>
        </div>
    </body>
</html>
