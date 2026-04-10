<x-app-layout>
    <x-slot name="header">Modifier BDC — {{ $bdc->numero }}</x-slot>

    <form method="POST" action="{{ route('bons-commande.update', $bdc) }}" class="space-y-6">
        @csrf @method('PUT')
        <input type="hidden" name="devis_id" value="{{ $bdc->devis_id }}">

        <script>
        window.addEventListener('combobox-selected', function(e) {
            if (e.detail.field === 'client_id') {
                window.dispatchEvent(new CustomEvent('combobox-update-endpoint', {
                    detail: { field: 'chantier_id', endpoint: '{{ route('chantiers.api-search') }}?client_id=' + e.detail.id }
                }));
                window.dispatchEvent(new CustomEvent('combobox-update-create-url', {
                    detail: { field: 'chantier_id', createUrl: '/api/clients/' + e.detail.id + '/chantiers/quick-create' }
                }));
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

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
            <h2 class="font-semibold text-gray-700 border-b pb-2">Informations générales</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-combobox
                        name="client_id"
                        label="Client"
                        :endpoint="route('clients.api-search')"
                        :value="old('client_id', $bdc->client_id)"
                        :text="$bdc->client?->nom ?? ''"
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
                        :endpoint="route('chantiers.api-search') . '?client_id=' . $bdc->client_id"
                        :value="old('chantier_id', $bdc->chantier_id)"
                        :text="$bdc->chantier?->nom ?? ''"
                        placeholder="Rechercher un chantier…"
                        :allow-create="true"
                        create-label="Nouveau chantier"
                        :create-url="'/api/clients/' . $bdc->client_id . '/chantiers/quick-create'"
                        :create-fields="[
                            ['name' => 'nom', 'label' => 'Nom du chantier', 'type' => 'text', 'required' => true],
                            ['name' => 'adresse_chantier', 'label' => 'Adresse', 'type' => 'text'],
                            ['name' => 'ville', 'label' => 'Ville', 'type' => 'text'],
                        ]"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="statut" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        @foreach(['en_attente' => 'En attente', 'valide' => 'Validé', 'en_cours' => 'En cours', 'termine' => 'Terminé', 'archive' => 'Archivé'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('statut', (string) $bdc->statut) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mode de règlement</label>
                    <select name="mode_paiement_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">—</option>
                        @foreach($modesPaiement as $m)
                            <option value="{{ $m->id }}" @selected(old('mode_paiement_id', $bdc->mode_paiement_id) == $m->id)>{{ $m->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date document *</label>
                    <input type="date" name="date_document" value="{{ old('date_document', $bdc->date_document->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Début travaux</label>
                    <input type="date" name="date_debut_travaux" value="{{ old('date_debut_travaux', $bdc->date_debut_travaux?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fin prévue</label>
                    <input type="date" name="date_fin_prevue" value="{{ old('date_fin_prevue', $bdc->date_fin_prevue?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Délai règlement (jours)</label>
                    <input type="number" name="delai_reglement" value="{{ old('delai_reglement', $bdc->delai_reglement) }}" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frais de port (€ HT)</label>
                    <input type="number" name="frais_port" value="{{ old('frais_port', $bdc->frais_port) }}" step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ristourne globale (%)</label>
                    <input type="number" name="ristourne_globale" value="{{ old('ristourne_globale', $bdc->ristourne_globale) }}" step="0.01" min="0" max="100"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Acompte (€)</label>
                    <input type="number" name="acompte" value="{{ old('acompte', $bdc->acompte) }}" step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('notes', $bdc->notes) }}</textarea>
            </div>
        </div>

        {{-- Lignes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700">Lignes de prestations</h2>
            </div>
            <x-lignes-document :lignes-initiales="$bdc->lignes" :taux-tva="$tauxTva" :tva-defaut="21"/>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('bons-commande.show', $bdc) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">← Retour</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Enregistrer
            </button>
        </div>
    </form>
</x-app-layout>
