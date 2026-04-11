<x-app-layout>
    <x-slot name="header">Nouveau chantier</x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('chantiers.store') }}" class="space-y-6">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
                <h2 class="font-semibold text-gray-700 border-b pb-2">Informations générales</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-combobox
                            name="client_id"
                            label="Client"
                            :endpoint="route('clients.api-search')"
                            :value="old('client_id', $clientSelectionne?->id)"
                            :text="$clientSelectionne?->nom ?? ''"
                            :required="true"
                            placeholder="Rechercher un client…"
                            :allow-create="true"
                            create-label="Nouveau client"
                            :create-url="route('clients.quick-create')"
                            :create-fields="[
                                ['name' => 'nom', 'label' => 'Nom', 'type' => 'text', 'required' => true],
                                ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                                ['name' => 'telephone', 'label' => 'Téléphone', 'type' => 'text'],
                                ['name' => 'ville', 'label' => 'Ville', 'type' => 'text'],
                            ]"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                        <select name="statut" required
                                class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 @error('statut') border-red-400 @enderror">
                            <option value="actif"   @selected(old('statut', 'actif') === 'actif')>Actif</option>
                            <option value="inactif" @selected(old('statut') === 'inactif')>Inactif</option>
                            <option value="termine" @selected(old('statut') === 'termine')>Terminé</option>
                            <option value="archive" @selected(old('statut') === 'archive')>Archivé</option>
                        </select>
                        @error('statut')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du chantier *</label>
                    <input type="text" name="nom" value="{{ old('nom') }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 @error('nom') border-red-400 @enderror">
                    @error('nom')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Référence chantier
                        <span class="text-xs text-gray-400 font-normal">— générée automatiquement si vide</span>
                    </label>
                    <input type="text" name="reference" value="{{ old('reference') }}"
                           placeholder="Ex: VD-2026-003"
                           maxlength="20"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono focus:ring-blue-500 focus:border-blue-500 @error('reference') border-red-400 @enderror">
                    @error('reference')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">Ce code est donné aux ouvriers pour identifier le chantier chez les fournisseurs. Laissez vide pour une génération automatique.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                        <input type="date" name="date_debut" value="{{ old('date_debut') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin prévue</label>
                        <input type="date" name="date_fin_prevue" value="{{ old('date_fin_prevue') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 @error('date_fin_prevue') border-red-400 @enderror">
                        @error('date_fin_prevue')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
                <h2 class="font-semibold text-gray-700 border-b pb-2">Adresse du chantier</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" name="adresse_chantier" value="{{ old('adresse_chantier') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code postal</label>
                        <input type="text" name="code_postal" value="{{ old('code_postal') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                        <input type="text" name="ville" value="{{ old('ville') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                        <input type="text" name="pays" value="{{ old('pays', 'Belgique') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
                <h2 class="font-semibold text-gray-700 border-b pb-2">Notes & marge</h2>
                <div>
                    <textarea name="notes" rows="4"
                              placeholder="Informations complémentaires, remarques…"
                              class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Coefficient de marge spécifique (%)
                        <span class="text-xs text-gray-400 font-normal">— laissez vide pour utiliser la marge du client</span>
                    </label>
                    <input type="number" name="coefficient_marge"
                           value="{{ old('coefficient_marge') }}"
                           step="0.01" min="0" max="200" placeholder="Ex: 15"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('chantiers.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                    ← Retour
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Créer le chantier
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
