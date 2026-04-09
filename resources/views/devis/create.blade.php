<x-app-layout>
    <x-slot name="header">Nouveau devis</x-slot>

    <form method="POST" action="{{ route('devis.store') }}"
          x-data="{
              clientId: '{{ old('client_id', $clientSelectionne?->id ?? '') }}',
              chantiers: @js($chantiers ?? collect()),
              chargerChantiers(id) {
                  if (!id) { this.chantiers = []; window.coefficientMargeActuel = 0; return; }
                  fetch('/api/clients/' + id + '/chantiers')
                      .then(r => r.json())
                      .then(data => { this.chantiers = data; });
              },
              changerChantier(id) {
                  const c = this.chantiers.find(c => c.id == id);
                  window.coefficientMargeActuel = c ? (c.coefficient_marge || 0) : 0;
              },
              openNewClient: false,
              openNewChantier: false,
              newClient: { nom: '', email: '', telephone: '', ville: '' },
              newChantier: { nom: '', adresse_chantier: '' },
              savingClient: false,
              savingChantier: false,
              async submitNewClient() {
                  this.savingClient = true;
                  const resp = await fetch('{{ route('clients.quick-create') }}', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                      body: JSON.stringify(this.newClient),
                  });
                  const data = await resp.json();
                  if (resp.ok) {
                      this.$refs.clientSelect.add(new Option(data.nom, data.id, true, true));
                      this.clientId = String(data.id);
                      this.chargerChantiers(data.id);
                      this.openNewClient = false;
                      this.newClient = { nom: '', email: '', telephone: '', ville: '' };
                  }
                  this.savingClient = false;
              },
              async submitNewChantier() {
                  if (!this.clientId) return;
                  this.savingChantier = true;
                  const resp = await fetch('/api/clients/' + this.clientId + '/chantiers/quick-create', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                      body: JSON.stringify(this.newChantier),
                  });
                  const data = await resp.json();
                  if (resp.ok) {
                      this.chantiers = [...this.chantiers, data];
                      this.openNewChantier = false;
                      this.newChantier = { nom: '', adresse_chantier: '' };
                      this.$nextTick(() => {
                          this.$refs.chantierSelect.value = data.id;
                          this.changerChantier(data.id);
                      });
                  }
                  this.savingChantier = false;
              },
          }"
          x-init="clientId && chargerChantiers(clientId)">
        @csrf

        {{-- Modals création à la volée --}}
        <div x-show="openNewClient" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div @click.outside="openNewClient = false" class="bg-white rounded-xl shadow-xl p-6 w-[440px] space-y-4">
                <h3 class="font-semibold text-gray-800">Nouveau client</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" x-model="newClient.nom" required autofocus
                               class="w-full rounded-lg border-gray-300 text-sm" placeholder="Nom ou raison sociale">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" x-model="newClient.email" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input type="text" x-model="newClient.telephone" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                        <input type="text" x-model="newClient.ville" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="openNewClient = false"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Annuler</button>
                    <button type="button" @click="submitNewClient()"
                            :disabled="savingClient || !newClient.nom"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <span x-text="savingClient ? 'Création…' : 'Créer le client'"></span>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="openNewChantier" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div @click.outside="openNewChantier = false" class="bg-white rounded-xl shadow-xl p-6 w-[440px] space-y-4">
                <h3 class="font-semibold text-gray-800">Nouveau chantier</h3>
                <p class="text-xs text-gray-400" x-show="!clientId">Sélectionnez d'abord un client.</p>
                <div class="space-y-3" x-show="clientId">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du chantier *</label>
                        <input type="text" x-model="newChantier.nom" required
                               class="w-full rounded-lg border-gray-300 text-sm" placeholder="Ex: Rénovation cuisine">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse chantier</label>
                        <input type="text" x-model="newChantier.adresse_chantier"
                               class="w-full rounded-lg border-gray-300 text-sm" placeholder="Rue et numéro">
                    </div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="openNewChantier = false"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Annuler</button>
                    <button type="button" @click="submitNewChantier()"
                            :disabled="savingChantier || !newChantier.nom || !clientId"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <span x-text="savingChantier ? 'Création…' : 'Créer le chantier'"></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Colonne principale --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- Client & Chantier --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Client & Chantier</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Client <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <select name="client_id" x-model="clientId" x-ref="clientSelect"
                                        @change="chargerChantiers(clientId)"
                                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Sélectionner un client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id', $clientSelectionne?->id) == $client->id ? 'selected' : '' }}>
                                            {{ $client->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" @click="openNewClient = true"
                                        title="Créer un nouveau client"
                                        class="shrink-0 w-9 h-9 flex items-center justify-center border border-gray-300 rounded-lg text-gray-500 hover:bg-blue-50 hover:border-blue-400 hover:text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                            @error('client_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chantier</label>
                            <div class="flex gap-2">
                                <select name="chantier_id" x-ref="chantierSelect"
                                        @change="changerChantier($event.target.value)"
                                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Aucun chantier</option>
                                    <template x-for="c in chantiers" :key="c.id">
                                        <option :value="c.id"
                                                :selected="c.id == {{ old('chantier_id', $chantierSelectionne?->id ?? 'null') }}"
                                                x-text="c.nom"></option>
                                    </template>
                                </select>
                                <button type="button" @click="openNewChantier = true"
                                        title="Créer un nouveau chantier"
                                        :disabled="!clientId"
                                        class="shrink-0 w-9 h-9 flex items-center justify-center border border-gray-300 rounded-lg text-gray-500 hover:bg-blue-50 hover:border-blue-400 hover:text-blue-600 disabled:opacity-40 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                            @error('chantier_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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
                        :tva-defaut="21"/>
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
