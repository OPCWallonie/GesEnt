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

    <form method="POST" action="{{ route('bons-commande.store') }}"
          x-data="{
              clientId: '{{ old('client_id', $devisSource->client_id ?? '') }}',
              chantiers: @js($chantiers ?? collect()),
              chargerChantiers(id) {
                  if (!id) { this.chantiers = []; return; }
                  fetch('/api/clients/' + id + '/chantiers')
                      .then(r => r.json())
                      .then(data => { this.chantiers = data; });
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
                      });
                  }
                  this.savingChantier = false;
              },
          }"
          x-init="clientId && chargerChantiers(clientId)">
        @csrf

        @isset($devisSource)
            <input type="hidden" name="devis_id" value="{{ $devisSource->id }}">
        @endisset

        {{-- Modals création à la volée --}}
        <div x-show="openNewClient" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div @click.outside="openNewClient = false" class="bg-white rounded-xl shadow-xl p-6 w-[440px] space-y-4">
                <h3 class="font-semibold text-gray-800">Nouveau client</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" x-model="newClient.nom" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Nom ou raison sociale">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" x-model="newClient.email" class="w-full rounded-lg border-gray-300 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input type="text" x-model="newClient.telephone" class="w-full rounded-lg border-gray-300 text-sm"></div>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                        <input type="text" x-model="newClient.ville" class="w-full rounded-lg border-gray-300 text-sm"></div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="openNewClient = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Annuler</button>
                    <button type="button" @click="submitNewClient()" :disabled="savingClient || !newClient.nom"
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
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Nom du chantier *</label>
                        <input type="text" x-model="newChantier.nom" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Ex: Rénovation cuisine"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Adresse chantier</label>
                        <input type="text" x-model="newChantier.adresse_chantier" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Rue et numéro"></div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="openNewChantier = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Annuler</button>
                    <button type="button" @click="submitNewChantier()" :disabled="savingChantier || !newChantier.nom || !clientId"
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
                                        <option value="{{ $client->id }}" {{ old('client_id', $devisSource->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                            {{ $client->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" @click="openNewClient = true" title="Créer un nouveau client"
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
                                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Aucun chantier</option>
                                    <template x-for="c in chantiers" :key="c.id">
                                        <option :value="c.id"
                                                :selected="c.id == {{ old('chantier_id', $devisSource->chantier_id ?? 'null') }}"
                                                x-text="c.nom"></option>
                                    </template>
                                </select>
                                <button type="button" @click="openNewChantier = true" title="Créer un nouveau chantier"
                                        :disabled="!clientId"
                                        class="shrink-0 w-9 h-9 flex items-center justify-center border border-gray-300 rounded-lg text-gray-500 hover:bg-blue-50 hover:border-blue-400 hover:text-blue-600 disabled:opacity-40 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                            @error('chantier_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Lignes du document --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Lignes du bon de commande</h2>
                    <x-lignes-document
                        :lignes-initiales="isset($devisSource) ? $devisSource->lignes : collect()"
                        :taux-tva="$tauxTva"
                        :tva-defaut="21"/>
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
