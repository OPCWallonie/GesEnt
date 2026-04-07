<x-app-layout>
    <x-slot name="header">Nouveau kit</x-slot>

    <form method="POST" action="{{ route('kits.store') }}">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4 mb-6">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du kit *</label>
                    <input type="text" name="nom" value="{{ old('nom') }}" required
                           placeholder="Ex: Salle de bain standard"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('nom')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <input type="text" name="categorie" value="{{ old('categorie') }}"
                           placeholder="Ex: Sanitaire, Chauffage…"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}"
                           placeholder="Courte description du contenu…"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700">Lignes du kit</h2>
                <p class="text-xs text-gray-400 mt-1">Saisissez les lignes type de ce kit. Les quantités et prix seront utilisés comme valeurs par défaut à l'insertion.</p>
            </div>
            <x-lignes-document
                :lignes-initiales="collect()"
                :taux-tva="\App\Models\TauxTva::orderBy('taux')->get()"
            />
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('kits.index') }}"
               class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                Annuler
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Créer le kit
            </button>
        </div>
    </form>
</x-app-layout>
