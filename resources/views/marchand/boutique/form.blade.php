<x-espace-layout :titre="$boutique?->exists ? 'Modifier la boutique' : 'Créer ma boutique'" actif="tableau">
    <form method="POST" action="{{ route('marchand.boutique.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="nom" value="Nom de la boutique" />
            <x-text-input id="nom" name="nom" :value="old('nom', $boutique?->nom)" required class="block mt-1 w-full min-h-[48px]" />
            <x-input-error :messages="$errors->get('nom')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Description" />
            <textarea id="description" name="description" rows="4" class="block mt-1 w-full rounded-xl border-bordure-champ text-encre shadow-sm focus:border-vert focus:ring-vert">{{ old('description', $boutique?->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="logo" value="Logo" />
            @if ($boutique?->logo_url)
                <img src="{{ $boutique->logo_url }}" alt="Logo actuel" class="mt-2 w-20 h-20 rounded-xl object-cover border border-bordure-carte">
            @endif
            <input id="logo" name="logo" type="file" accept="image/*" class="block mt-1 w-full text-sm text-secondaire file:mr-4 file:min-h-[48px] file:px-4 file:rounded-xl file:border-0 file:bg-vert-clair file:text-vert file:font-semibold">
            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">{{ $boutique?->exists ? 'Enregistrer' : 'Créer la boutique' }}</x-primary-button>
    </form>
</x-espace-layout>
