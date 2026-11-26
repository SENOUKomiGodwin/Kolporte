<x-guest-layout>
    <h1 class="font-display text-2xl font-bold mb-1">Créer un compte</h1>
    <p class="text-sm text-secondaire mb-6">Rejoignez Kolporte en quelques secondes.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nom -->
        <div>
            <x-input-label for="name" value="Nom complet" />
            <x-text-input id="name" class="block mt-1 w-full min-h-[48px]" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Téléphone -->
        <div class="mt-4">
            <x-input-label for="telephone" value="Téléphone" />
            <x-text-input id="telephone" class="block mt-1 w-full min-h-[48px]" type="tel" name="telephone" :value="old('telephone')" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('telephone')" class="mt-2" />
        </div>

        <!-- E-mail -->
        <div class="mt-4">
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="block mt-1 w-full min-h-[48px]" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Rôle -->
        <div class="mt-4">
            <x-input-label value="Je suis" />
            <div class="mt-2 grid grid-cols-2 gap-3">
                <label class="flex items-center justify-center min-h-[48px] px-4 rounded-xl border cursor-pointer has-[:checked]:border-vert has-[:checked]:bg-vert-clair has-[:checked]:text-vert">
                    <input type="radio" name="role" value="marchand" class="sr-only" @checked(old('role', 'marchand') === 'marchand') required>
                    <span class="text-sm font-semibold">Un marchand</span>
                </label>
                <label class="flex items-center justify-center min-h-[48px] px-4 rounded-xl border cursor-pointer has-[:checked]:border-vert has-[:checked]:bg-vert-clair has-[:checked]:text-vert">
                    <input type="radio" name="role" value="promoteur" class="sr-only" @checked(old('role') === 'promoteur')>
                    <span class="text-sm font-semibold">Un promoteur</span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Mot de passe -->
        <div class="mt-4">
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input id="password" class="block mt-1 w-full min-h-[48px]" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmation -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full min-h-[48px]" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full">
                {{ __('S’inscrire') }}
            </x-primary-button>
        </div>

        <p class="mt-4 text-center text-sm text-secondaire">
            Déjà inscrit ?
            <a class="font-semibold text-vert hover:underline" href="{{ route('login') }}">Se connecter</a>
        </p>
    </form>
</x-guest-layout>
