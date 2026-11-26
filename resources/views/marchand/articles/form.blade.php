<x-espace-layout :titre="$article->exists ? 'Modifier l’article' : 'Nouvel article'" actif="articles">
    <form method="POST"
          action="{{ $article->exists ? route('marchand.articles.update', $article) : route('marchand.articles.store') }}"
          enctype="multipart/form-data"
          class="space-y-5"
          x-data="{
              prix: {{ old('prix', $article->prix ?? 0) }},
              type: '{{ old('type_commission', $article->type_commission ?? 'pourcentage') }}',
              valeur: {{ old('valeur_commission', $article->valeur_commission ?? 0) }},
              gain() {
                  const p = parseFloat(this.prix) || 0;
                  const v = parseFloat(this.valeur) || 0;
                  return this.type === 'pourcentage' ? p * v / 100 : v;
              }
          }">
        @csrf
        @if ($article->exists)
            @method('PUT')
        @endif

        <div>
            <x-input-label for="titre" value="Titre" />
            <x-text-input id="titre" name="titre" :value="old('titre', $article->titre)" required class="block mt-1 w-full min-h-[48px]" />
            <x-input-error :messages="$errors->get('titre')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Description" />
            <textarea id="description" name="description" rows="4" class="block mt-1 w-full rounded-xl border-bordure-champ text-encre shadow-sm focus:border-vert focus:ring-vert">{{ old('description', $article->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <x-input-label for="prix" value="Prix (FCFA)" />
                <x-text-input id="prix" name="prix" type="number" min="1" step="1" x-model.number="prix" :value="old('prix', $article->prix)" required class="block mt-1 w-full min-h-[48px]" />
                <x-input-error :messages="$errors->get('prix')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="stock" value="Stock" />
                <x-text-input id="stock" name="stock" type="number" min="0" step="1" :value="old('stock', $article->stock)" required class="block mt-1 w-full min-h-[48px]" />
                <x-input-error :messages="$errors->get('stock')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="photo" :value="$article->exists ? 'Photo' : 'Photo'" />
            @if ($article->photo_url)
                <img src="{{ $article->photo_url }}" alt="Photo actuelle" class="mt-2 w-24 h-24 rounded-xl object-cover border border-bordure-carte">
            @endif
            <input id="photo" name="photo" type="file" accept="image/*" class="block mt-1 w-full text-sm text-secondaire file:mr-4 file:min-h-[48px] file:px-4 file:rounded-xl file:border-0 file:bg-vert-clair file:text-vert file:font-semibold">
            <x-input-error :messages="$errors->get('photo')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="type_commission" value="Type de commission" />
            <select id="type_commission" name="type_commission" x-model="type" class="block mt-1 w-full min-h-[48px] rounded-xl border-bordure-champ text-encre shadow-sm focus:border-vert focus:ring-vert">
                <option value="pourcentage">Pourcentage du prix</option>
                <option value="fixe">Montant fixe par vente</option>
            </select>
            <x-input-error :messages="$errors->get('type_commission')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="valeur_commission" value="Valeur de la commission" />
            <x-text-input id="valeur_commission" name="valeur_commission" type="number" min="0" step="any" x-model.number="valeur" :value="old('valeur_commission', $article->valeur_commission)" required class="block mt-1 w-full min-h-[48px]" />
            <x-input-error :messages="$errors->get('valeur_commission')" class="mt-2" />
        </div>

        <p class="px-4 py-3 rounded-xl bg-vert-clair text-vert text-sm font-medium">
            Le promoteur gagne <span x-text="Math.round(gain()).toLocaleString('fr-FR')"></span> FCFA par vente.
        </p>

        <x-primary-button class="w-full">{{ $article->exists ? 'Enregistrer' : 'Publier l’article' }}</x-primary-button>
    </form>
</x-espace-layout>
