<x-app-layout>
    <x-slot name="header">Nouveau devis</x-slot>

    <form method="POST" action="{{ route('devis.store') }}">
        @csrf

        <script>
        window.coefficientMargeActuel = {{ ($chantierSelectionne?->coefficientMargeEffectif()) ?? 0 }};
        window.addEventListener('combobox-selected', function(e) {
            if (e.detail.field === 'client_id') {
                window.dispatchEvent(new CustomEvent('combobox-update-endpoint', {
                    detail: { field: 'chantier_id', endpoint: '{{ route('chantiers.api-search') }}?client_id=' + e.detail.id }
                }));
                window.dispatchEvent(new CustomEvent('combobox-update-create-url', {
                    detail: { field: 'chantier_id', createUrl: '/api/clients/' + e.detail.id + '/chantiers/quick-create' }
                }));
                window.dispatchEvent(new CustomEvent('combobox-trigger-search', { detail: { field: 'chantier_id' } }));
            }
            if (e.detail.field === 'chantier_id') {
                window.coefficientMargeActuel = e.detail.item.coefficient_marge || 0;
            }
        });
        window.addEventListener('combobox-cleared', function(e) {
            if (e.detail.field === 'chantier_id') window.coefficientMargeActuel = 0;
            if (e.detail.field === 'client_id') {
                window.dispatchEvent(new CustomEvent('combobox-update-create-url', {
                    detail: { field: 'chantier_id', createUrl: null }
                }));
            }
        });
        </script>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Colonne principale --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- Client & Chantier --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Client & Chantier</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                            <x-combobox
                                name="chantier_id"
                                label="Chantier"
                                :endpoint="route('chantiers.api-search') . ($clientSelectionne ? '?client_id=' . $clientSelectionne->id : '')"
                                :value="old('chantier_id', $chantierSelectionne?->id)"
                                :text="$chantierSelectionne?->nom ?? ''"
                                placeholder="Rechercher un chantier…"
                                :allow-create="true"
                                create-label="Nouveau chantier"
                                :create-url="$clientSelectionne ? '/api/clients/' . $clientSelectionne->id . '/chantiers/quick-create' : null"
                                :create-fields="[
                                    ['name' => 'nom', 'label' => 'Nom du chantier', 'type' => 'text', 'required' => true],
                                    ['name' => 'adresse_chantier', 'label' => 'Adresse', 'type' => 'text'],
                                    ['name' => 'ville', 'label' => 'Ville', 'type' => 'text'],
                                ]"
                            />
                        </div>
                    </div>
                </div>

                {{-- Bandeau Produits habituels (8D) --}}
                <div x-data="produitsHabituels()" x-init="charger()" x-show="produits.length > 0" x-cloak
                     class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-blue-800">Vos produits habituels</h3>
                        <span class="text-xs text-blue-500">Cliquez pour ajouter directement</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="p in produits" :key="(p.source || 'i') + p.id">
                            <button @click="ajouterProduit(p)" type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-blue-200 rounded-lg text-sm text-gray-700 hover:bg-blue-100 hover:border-blue-300 transition-colors">
                                <span x-text="p.designation" class="truncate max-w-48"></span>
                                <span class="text-xs text-gray-400" x-text="(p.prix || p.prix_unitaire || 0).toFixed(2) + ' €'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Lignes du document --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Lignes du devis</h2>
                    <x-lignes-document
                        :lignes-initiales="collect()"
                        :taux-tva="$tauxTva"
                        :tva-defaut="21"
                        :client-id="$clientSelectionne?->id ?? null"/>
                </div>

                {{-- Notes --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Notes</h2>
                    <textarea name="notes" rows="4"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Notes internes ou à destination du client…">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- Colonne latérale --}}
            <div class="space-y-6">

                {{-- Informations --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700">Informations</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date du document</label>
                        <input type="date" name="date_document"
                               value="{{ old('date_document', date('Y-m-d')) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('date_document')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de validité</label>
                        <input type="date" name="date_validite"
                               value="{{ old('date_validite', date('Y-m-d', strtotime('+' . ($parametres->validite_devis_defaut ?? 30) . ' days'))) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('date_validite')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select name="statut"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="brouillon" {{ old('statut', 'brouillon') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                            <option value="en_attente" {{ old('statut') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                            <option value="valide" {{ old('statut') === 'valide' ? 'selected' : '' }}>Validé</option>
                        </select>
                        @error('statut')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Conditions de paiement --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700">Conditions de paiement</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement</label>
                        <select name="mode_paiement_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Sélectionner —</option>
                            @foreach($modesPaiement as $mp)
                                <option value="{{ $mp->id }}" {{ old('mode_paiement_id') == $mp->id ? 'selected' : '' }}>
                                    {{ $mp->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Délai de règlement (jours)</label>
                        <input type="number" name="delai_reglement" min="0"
                               value="{{ old('delai_reglement', $parametres->delai_reglement_defaut ?? 30) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frais de port (€)</label>
                        <input type="number" name="frais_port" min="0" step="0.01"
                               value="{{ old('frais_port', 0) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ristourne globale (%)</label>
                        <input type="number" name="ristourne_globale" min="0" max="100" step="0.01"
                               value="{{ old('ristourne_globale', 0) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Acompte (€)</label>
                        <input type="number" name="acompte" min="0" step="0.01"
                               value="{{ old('acompte', 0) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col gap-2">
                    <button type="submit"
                            class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-medium text-center">
                        Enregistrer le devis
                    </button>
                    <a href="{{ route('devis.index') }}"
                       class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium text-center">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </form>

<script>
function produitsHabituels() {
    return {
        produits: [],
        async charger() {
            try {
                const r = await fetch('{{ route("produits.suggestions") }}');
                this.produits = await r.json();
            } catch (e) {}
        },
        ajouterProduit(p) {
            window.dispatchEvent(new CustomEvent('ajouter-produit', { detail: p }));
        }
    };
}
</script>
</x-app-layout>
