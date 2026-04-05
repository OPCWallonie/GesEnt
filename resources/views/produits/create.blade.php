<x-app-layout>
    <x-slot name="header">Nouveau produit</x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('produits.store') }}" class="space-y-6">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
                <h2 class="font-semibold text-gray-700 border-b pb-2">Identification</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Référence interne</label>
                        <input type="text" name="reference" value="{{ old('reference') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono focus:ring-blue-500 focus:border-blue-500 @error('reference') border-red-400 @enderror"
                               placeholder="REF-001">
                        @error('reference')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <input type="text" name="categorie" value="{{ old('categorie') }}"
                               list="liste-categories"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ex : Main d'œuvre, Matériau…">
                        <datalist id="liste-categories">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Désignation *</label>
                    <input type="text" name="designation" value="{{ old('designation') }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 @error('designation') border-red-400 @enderror">
                    @error('designation')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
                <h2 class="font-semibold text-gray-700 border-b pb-2">Tarification</h2>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unité *</label>
                        <input type="text" name="unite" value="{{ old('unite') }}" required
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 @error('unite') border-red-400 @enderror"
                               placeholder="u, m², h, m…">
                        @error('unite')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix unitaire HT *</label>
                        <div class="relative">
                            <input type="number" name="prix_unitaire" value="{{ old('prix_unitaire') }}" required
                                   step="0.0001" min="0"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 @error('prix_unitaire') border-red-400 @enderror pr-8">
                            <span class="absolute right-3 top-2 text-gray-400 text-sm">€</span>
                        </div>
                        @error('prix_unitaire')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Taux TVA *</label>
                        <select name="taux_tva" required
                                class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 @error('taux_tva') border-red-400 @enderror">
                            @foreach([21, 12, 6, 0] as $taux)
                                <option value="{{ $taux }}" @selected(old('taux_tva', 21) == $taux)>{{ $taux }} %</option>
                            @endforeach
                        </select>
                        @error('taux_tva')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
                <h2 class="font-semibold text-gray-700 border-b pb-2">Fournisseur</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur</label>
                        <input type="text" name="fournisseur" value="{{ old('fournisseur') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Référence fournisseur</label>
                        <input type="text" name="reference_fournisseur" value="{{ old('reference_fournisseur') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('produits.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                    ← Retour
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Créer le produit
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
