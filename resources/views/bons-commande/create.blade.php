<x-app-layout>
    <x-slot name="header">Nouveau bon de commande</x-slot>

    @isset($devisSource)
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Créé depuis le devis <a href="{{ route('devis.show', $devisSource) }}" class="font-semibold underline">{{ $devisSource->numero }}</a>
        </div>
    @endisset

    <form method="POST" action="{{ route('bons-commande.store') }}">
        @csrf

        @isset($devisSource)
            <input type="hidden" name="devis_id" value="{{ $devisSource->id }}">
        @endisset

        <script>
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
        });
        window.addEventListener('combobox-cleared', function(e) {
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
                                :value="old('client_id', $devisSource->client_id ?? null)"
                                :text="$devisSource->client->nom ?? ''"
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
                                :endpoint="route('chantiers.api-search') . (isset($devisSource) && $devisSource->client_id ? '?client_id=' . $devisSource->client_id : '')"
                                :value="old('chantier_id', $devisSource->chantier_id ?? null)"
                                :text="isset($devisSource) && $devisSource->chantier ? $devisSource->chantier->nom : ''"
                                placeholder="Rechercher un chantier…"
                                :allow-create="true"
                                create-label="Nouveau chantier"
                                :create-url="isset($devisSource) && $devisSource->client_id ? '/api/clients/' . $devisSource->client_id . '/chantiers/quick-create' : null"
                                :create-fields="[
                                    ['name' => 'nom', 'label' => 'Nom du chantier', 'type' => 'text', 'required' => true],
                                    ['name' => 'adresse_chantier', 'label' => 'Adresse', 'type' => 'text'],
                                    ['name' => 'ville', 'label' => 'Ville', 'type' => 'text'],
                                ]"
                            />
                        </div>
                    </div>
                </div>

                {{-- Lignes du document --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Lignes du bon de commande</h2>
                    <x-lignes-document
                        :lignes-initiales="isset($devisSource) ? $devisSource->lignes : collect()"
                        :taux-tva="$tauxTva"
                        :tva-defaut="21"
                        :client-id="$devisSource->client_id ?? null"/>
                </div>

                {{-- Notes --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Notes</h2>
                    <textarea name="notes" rows="4"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Notes internes ou à destination du client…">{{ old('notes', $devisSource->notes ?? '') }}</textarea>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date début travaux</label>
                        <input type="date" name="date_debut_travaux"
                               value="{{ old('date_debut_travaux') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('date_debut_travaux')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date fin prévue</label>
                        <input type="date" name="date_fin_prevue"
                               value="{{ old('date_fin_prevue') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('date_fin_prevue')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select name="statut"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="en_attente" {{ old('statut', 'en_attente') === 'en_attente' ? 'selected' : '' }}>En attente</option>
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
                                <option value="{{ $mp->id }}" {{ old('mode_paiement_id', $devisSource->mode_paiement_id ?? '') == $mp->id ? 'selected' : '' }}>
                                    {{ $mp->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Délai de règlement (jours)</label>
                        <input type="number" name="delai_reglement" min="0"
                               value="{{ old('delai_reglement', $devisSource->delai_reglement ?? $parametres->delai_reglement_defaut ?? 30) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frais de port (€)</label>
                        <input type="number" name="frais_port" min="0" step="0.01"
                               value="{{ old('frais_port', $devisSource->frais_port ?? 0) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ristourne globale (%)</label>
                        <input type="number" name="ristourne_globale" min="0" max="100" step="0.01"
                               value="{{ old('ristourne_globale', $devisSource->ristourne_globale ?? 0) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Acompte (€)</label>
                        <input type="number" name="acompte" min="0" step="0.01"
                               value="{{ old('acompte', $devisSource->acompte ?? 0) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col gap-2">
                    <button type="submit"
                            class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-medium text-center">
                        Enregistrer le BDC
                    </button>
                    <a href="{{ route('bons-commande.index') }}"
                       class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium text-center">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
