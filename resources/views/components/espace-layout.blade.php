@props(['titre' => 'Kolporte', 'actif' => null])

@php
    $user = auth()->user();
    $role = $user?->role ?? 'promoteur';

    $icones = [
        'grid' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
        'lien' => 'M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244',
        'portefeuille' => 'M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3',
        'profil' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        'tableau' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75',
        'articles' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        'commandes' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z',
        'retraits' => 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5',
        'utilisateurs' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    ];

    $navs = [
        'promoteur' => [
            ['route' => 'promoteur', 'label' => 'Catalogue', 'icone' => 'grid', 'actif' => 'catalogue'],
            ['route' => 'promoteur.liens', 'label' => 'Mes liens', 'icone' => 'lien', 'actif' => 'liens'],
            ['route' => 'promoteur.portefeuille', 'label' => 'Portefeuille', 'icone' => 'portefeuille', 'actif' => 'portefeuille'],
            ['route' => 'profile.edit', 'label' => 'Profil', 'icone' => 'profil', 'actif' => 'profil'],
        ],
        'marchand' => [
            ['route' => 'marchand', 'label' => 'Tableau', 'icone' => 'tableau', 'actif' => 'tableau'],
            ['route' => 'marchand.articles', 'label' => 'Articles', 'icone' => 'articles', 'actif' => 'articles'],
            ['route' => 'marchand.commandes', 'label' => 'Commandes', 'icone' => 'commandes', 'actif' => 'commandes'],
            ['route' => 'profile.edit', 'label' => 'Profil', 'icone' => 'profil', 'actif' => 'profil'],
        ],
        'admin' => [
            ['route' => 'admin.retraits', 'label' => 'Retraits', 'icone' => 'retraits', 'actif' => 'retraits'],
            ['route' => 'admin.utilisateurs', 'label' => 'Utilisateurs', 'icone' => 'utilisateurs', 'actif' => 'utilisateurs'],
        ],
    ];

    $items = $navs[$role] ?? [];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $titre }} · Kolporte</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..700&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans bg-fond text-encre antialiased">
        <div class="min-h-screen flex flex-col max-w-md mx-auto">
            <header class="sticky top-0 z-10 bg-fond/95 backdrop-blur border-b border-bordure-carte">
                <div class="flex items-center justify-between px-4 h-14">
                    <a href="{{ route($role) }}" class="font-display text-xl font-bold text-vert">Kolporte</a>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-secondaire">{{ $user?->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="min-h-[44px] px-3 text-sm font-medium text-vert hover:underline">Quitter</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 pb-28">
                <h1 class="font-display text-2xl font-bold mb-6">{{ $titre }}</h1>
                {{ $slot }}
            </main>

            <nav class="fixed bottom-0 inset-x-0 z-10 bg-white border-t border-bordure-carte">
                <div class="max-w-md mx-auto grid {{ count($items) === 2 ? 'grid-cols-2' : 'grid-cols-4' }}">
                    @foreach ($items as $item)
                        @php $estActif = $actif === $item['actif']; @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex flex-col items-center justify-center gap-1 min-h-[64px] py-2 text-[11px] {{ $estActif ? 'text-vert font-semibold' : 'text-secondaire' }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icones[$item['icone']] }}" />
                            </svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </nav>
        </div>
    </body>
</html>
