<x-app-layout>
    <x-slot name="header">{{ $scenario->exists ? 'Modifier : '.$scenario->nom : 'Nouveau scénario' }}</x-slot>

    <div class="max-w-3xl"
         x-data="relanceScenarioEditor({{ $scenario->exists ? $scenario->etapes->toJson() : '[]' }})">

        <form method="POST"
              action="{{ $scenario->exists ? route('relance-scenarios.update', $scenario) : route('relance-scenarios.store') }}"
              @submit.prevent="soumettre($el)">
            @csrf
            @if($scenario->exists) @method('PUT') @endif

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Informations générales --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-4 mb-5">
                <h2 class="font-semibold text-gray-700">Informations</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                    <input type="text" name="nom" required
                           value="{{ old('nom', $scenario->nom) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2"
                              class="w-full rounded-lg border-gray-300 text-sm">{{ old('description', $scenario->description) }}</textarea>
                </div>
            </div>

            {{-- Étapes --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-700">Étapes de relance</h2>
                    <button type="button" @click="ajouterEtape()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-indigo-300 text-indigo-600 rounded-lg hover:bg-indigo-50">
                        + Ajouter une étape
                    </button>
                </div>

                <div class="divide-y divide-gray-100">
                    <template x-for="(etape, index) in etapes" :key="etape._id">
                        <div class="p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="font-medium text-sm text-gray-700">
                                    Étape <span x-text="index + 1"></span>
                                </h3>
                                <div class="flex items-center gap-3">
                                    <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                        <input type="checkbox" x-model="etape.actif" class="rounded border-gray-300 text-indigo-600">
                                        <span class="text-gray-600">Active</span>
                                    </label>
                                    <button type="button" @click="supprimerEtape(index)"
                                            x-show="etapes.length > 1"
                                            class="text-xs text-red-500 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50">
                                        Supprimer
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Délai (jours après échéance) *</label>
                                    <input type="number" x-model.number="etape.delai_jours" min="1" required
                                           class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Canal *</label>
                                    <select x-model="etape.canal" class="w-full rounded-lg border-gray-300 text-sm">
                                        <option value="mail">Email uniquement</option>
                                        <option value="courrier">Courrier PDF uniquement</option>
                                        <option value="les_deux">Email + Courrier PDF</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Ton *</label>
                                    <select x-model="etape.ton" class="w-full rounded-lg border-gray-300 text-sm">
                                        <option value="cordial">Cordial</option>
                                        <option value="ferme">Ferme</option>
                                        <option value="formel">Formel (mise en demeure)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Sujet de l'email *</label>
                                <input type="text" x-model="etape.sujet" required
                                       class="w-full rounded-lg border-gray-300 text-sm"
                                       placeholder="Ex : Rappel — Facture {numero}">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Corps de l'email *</label>
                                <textarea x-model="etape.corps_email" rows="5" required
                                          class="w-full rounded-lg border-gray-300 text-sm font-mono text-xs"></textarea>
                            </div>

                            <p class="text-xs text-gray-400">
                                Variables disponibles :
                                <code class="bg-gray-100 px-1 rounded">{client}</code>
                                <code class="bg-gray-100 px-1 rounded">{numero}</code>
                                <code class="bg-gray-100 px-1 rounded">{solde_du}</code>
                                <code class="bg-gray-100 px-1 rounded">{jours_retard}</code>
                                <code class="bg-gray-100 px-1 rounded">{date_facture}</code>
                                <code class="bg-gray-100 px-1 rounded">{chantier}</code>
                                <code class="bg-gray-100 px-1 rounded">{entreprise}</code>
                            </p>
                        </div>
                    </template>

                    <div x-show="etapes.length === 0" class="p-8 text-center text-gray-400 text-sm">
                        Aucune étape — cliquez sur « Ajouter une étape »
                    </div>
                </div>

                {{-- Champs cachés sérialisés --}}
                <div id="etapes-hidden"></div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('relance-scenarios.index') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    {{ $scenario->exists ? 'Enregistrer' : 'Créer le scénario' }}
                </button>
            </div>
        </form>
    </div>

    <script>
    function relanceScenarioEditor(etapesInitiales) {
        let _counter = 1000;

        const defaults = {
            sujet: 'Relance — Facture {numero}',
            corps_email: "Bonjour {client},\n\nNotre facture {numero} d'un montant de {solde_du} reste impayée à ce jour ({jours_retard} jours de retard).\n\nNous vous remercions de régulariser cette situation.\n\nCordialement,\n{entreprise}",
            canal: 'mail',
            ton: 'cordial',
            actif: true,
        };

        return {
            etapes: etapesInitiales.map(e => ({ ...e, _id: _counter++ })),

            ajouterEtape() {
                const dernierDelai = this.etapes.length
                    ? Math.max(...this.etapes.map(e => e.delai_jours)) + 14
                    : 7;
                this.etapes.push({
                    _id:          _counter++,
                    delai_jours:  dernierDelai,
                    numero_ordre: this.etapes.length + 1,
                    ...defaults,
                });
            },

            supprimerEtape(index) {
                this.etapes.splice(index, 1);
            },

            soumettre(form) {
                // Injecter les champs cachés
                const container = document.getElementById('etapes-hidden');
                container.innerHTML = '';

                this.etapes.forEach((etape, i) => {
                    const fields = {
                        [`etapes[${i}][numero_ordre]`]: i + 1,
                        [`etapes[${i}][delai_jours]`]:  etape.delai_jours,
                        [`etapes[${i}][sujet]`]:        etape.sujet,
                        [`etapes[${i}][corps_email]`]:  etape.corps_email,
                        [`etapes[${i}][canal]`]:        etape.canal,
                        [`etapes[${i}][ton]`]:          etape.ton,
                        [`etapes[${i}][actif]`]:        etape.actif ? '1' : '0',
                    };
                    for (const [name, value] of Object.entries(fields)) {
                        const input = document.createElement('input');
                        input.type  = 'hidden';
                        input.name  = name;
                        input.value = value;
                        container.appendChild(input);
                    }
                });

                form.submit();
            },
        };
    }
    </script>
</x-app-layout>
