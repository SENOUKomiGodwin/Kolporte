<x-guest-layout>
    <h1 class="font-display text-2xl font-bold mb-1">Connexion</h1>
    <p class="text-sm text-secondaire mb-6">Accédez à votre espace Kolporte.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- E-mail ou téléphone -->
        <div>
            <x-input-label for="email" value="E-mail ou téléphone" />
            <x-text-input id="email" class="block mt-1 w-full min-h-[48px]" type="text" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Mot de passe -->
        <div class="mt-4">
            <x-input-label for="password" value="Mot de passe" />

            <x-text-input id="password" class="block mt-1 w-full min-h-[48px]"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Se souvenir de moi -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center min-h-[44px]">
                <input id="remember_me" type="checkbox" class="rounded border-bordure-champ text-vert shadow-sm focus:ring-vert" name="remember">
                <span class="ms-2 text-sm text-secondaire">{{ __('Se souvenir de moi') }}</span>
            </label>
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full">
                {{ __('Se connecter') }}
            </x-primary-button>
        </div>

        <div class="flex items-center justify-between mt-4 text-sm">
            @if (Route::has('password.request'))
                <a class="text-vert hover:underline" href="{{ route('password.request') }}">
                    {{ __('Mot de passe oublié ?') }}
                </a>
            @endif
            @if (Route::has('register'))
                <a class="text-vert hover:underline" href="{{ route('register') }}">
                    {{ __('Créer un compte') }}
                </a>
            @endif
        </div>
    </form>
</x-guest-layout>
