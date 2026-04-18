{{--
    Composant réutilisable pour la saisie des lignes d'un document.
    Props :
      $lignesInitiales : collection de LigneDocument (pour l'édition)
      $tauxTva : collection de TauxTva
      $tvaDefaut : taux par défaut (ex: 21)
--}}
@props(['lignesInitiales' => collect(), 'tauxTva', 'tvaDefaut' => 21, 'clientId' => null])

<div x-data="lignesDocument({{ json_encode($lignesInitiales->map(fn($l) => [
    'designation'        => $l->designation,
    'detail'             => $l->detail ?? '',
    'unite'              => $l->unite,
    'quantite'           => $l->quantite,
    'prix_unitaire'      => $l->prix_unitaire,
    'remise_valeur'      => $l->remise_valeur,
    'remise_type'        => $l->remise_type,
    'taux_tva'           => $l->taux_tva,
    'est_section'        => $l->est_section,
    'montant_ht'         => $l->montant_ht,
    'produit_id'         => $l->produit_id,
    'catalog_produit_id' => $l->catalog_produit_id ?? null,
    'produit_key'        => $l->produit_id ? 'p:' . $l->produit_id : ($l->catalog_produit_id ?? null ? 'c:' . ($l->catalog_produit_id ?? '') : ''),
])->values()) }}, {{ $tvaDefaut }}, {{ $clientId ?? 'null' }})">

    {{-- En-tête tableau --}}
    <div class="hidden md:grid grid-cols-12 gap-2 px-3 py-2 bg-gray-50 border-b border-gray-200 text-xs font-medium text-gray-500 uppercase">
        <div class="col-span-4">Désignation / Détail</div>
        <div class="col-span-1 text-center">Unité</div>
        <div class="col-span-1 text-right">Qté</div>
        <div class="col-span-1 text-right">Prix HT</div>
        <div class="col-span-2 text-right">Remise</div>
        <div class="col-span-1 text-right">TVA %</div>
        <div class="col-span-1 text-right">Total HT</div>
        <div class="col-span-1"></div>
    </div>

    {{-- Lignes --}}
    <div x-ref="lignesSortable" class="lignes-sortable-container">
    <template x-for="(ligne, index) in lignes" :key="ligne._uid ?? index">
        <div class="border-b border-gray-100 hover:bg-gray-50" :class="ligne.est_section ? 'bg-blue-50' : ''">

            {{-- Ligne titre/section --}}
            <template x-if="ligne.est_section">
                <div class="flex items-center gap-2 px-3 py-2">
                    <div class="ligne-drag-handle flex items-center justify-center text-gray-300 hover:text-gray-500 cursor-grab active:cursor-grabbing select-none flex-shrink-0" title="Glisser pour réorganiser">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 012 2v12a2 2 0 01-4 0V4a2 2 0 012-2zM13 2a2 2 0 012 2v12a2 2 0 01-4 0V4a2 2 0 012-2z"/></svg>
                    </div>
                    <input type="hidden" :name="`lignes[${index}][est_section]`" value="1">
                    <input :name="`lignes[${index}][designation]`" x-model="ligne.designation"
                           placeholder="Titre de section…"
                           class="flex-1 font-semibold text-sm border-0 bg-transparent focus:ring-1 focus:ring-blue-300 rounded px-2 py-1 text-blue-800">
                    <input type="hidden" :name="`lignes[${index}][detail]`" value="">
                    <input type="hidden" :name="`lignes[${index}][unite]`" value="—">
                    <input type="hidden" :name="`lignes[${index}][quantite]`" value="0">
                    <input type="hidden" :name="`lignes[${index}][prix_unitaire]`" value="0">
                    <input type="hidden" :name="`lignes[${index}][remise_valeur]`" value="0">
                    <input type="hidden" :name="`lignes[${index}][remise_type]`" value="montant">
                    <input type="hidden" :name="`lignes[${index}][taux_tva]`" value="{{ $tvaDefaut }}">
                    <input type="hidden" :name="`lignes[${index}][produit_id]`" :value="ligne.produit_id || ''">
                    <input type="hidden" :name="`lignes[${index}][catalog_produit_id]`" :value="ligne.catalog_produit_id || ''">
                    <button type="button" @click="supprimerLigne(index)" class="text-red-400 hover:text-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>

            {{-- Ligne produit normale --}}
            <template x-if="!ligne.est_section">
                <div class="p-3 space-y-2">
                    <div class="grid grid-cols-12 gap-2 items-start">
                        {{-- Désignation + autocomplete intelligent --}}
                        <div class="col-span-12 md:col-span-4 relative" x-data="{ suggestions: [], loading: false }">
                            <input :name="`lignes[${index}][designation]`"
                                   x-model="ligne.designation"
                                   @input.debounce.300ms="rechercherProduit($event.target.value, index, res => { $data.suggestions = res })"
                                   @focus="if (ligne.designation.length === 0) rechercherProduit('', index, res => { $data.suggestions = res })"
                                   @blur.debounce.200ms="suggestions = []"
                                   placeholder="Désignation du produit…"
                                   class="w-full text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 px-2 py-1.5"
                                   autocomplete="off">
                            <div x-show="suggestions.length > 0"
                                 class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg text-sm max-h-64 overflow-y-auto">
                                <template x-for="s in suggestions" :key="(s.source || 'i') + s.id">
                                    <div @click="selectionnerProduit(index, s); suggestions = []"
                                         class="px-3 py-2 hover:bg-blue-50 cursor-pointer flex items-center justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium truncate" x-text="s.designation"></div>
                                            <div class="text-xs text-gray-400" x-text="(s.fournisseur ? s.fournisseur + (s.reference ? ' — ' + s.reference : '') + ' · ' : '') + (s.prix || s.prix_unitaire || 0).toFixed(2) + ' € / ' + (s.unite || 'pièce') + ' — TVA ' + s.taux_tva + '%'"></div>
                                        </div>
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <span x-show="s.habituel"
                                                  class="text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded">Habituel</span>
                                            <span x-show="s.associe && !s.habituel"
                                                  class="text-xs bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded">Souvent avec</span>
                                            <span x-show="s.en_stock"
                                                  class="w-2 h-2 bg-green-400 rounded-full" title="En stock"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <input type="hidden" :name="`lignes[${index}][produit_id]`" :value="ligne.produit_id || ''">
                            <input type="hidden" :name="`lignes[${index}][catalog_produit_id]`" :value="ligne.catalog_produit_id || ''">
                        </div>

                        {{-- Unité --}}
                        <div class="col-span-6 md:col-span-1">
                            <input :name="`lignes[${index}][unite]`" x-model="ligne.unite"
                                   placeholder="pièce" class="w-full text-sm border-gray-300 rounded px-2 py-1.5 text-center">
                        </div>

                        {{-- Quantité --}}
                        <div class="col-span-6 md:col-span-1">
                            <input type="text" :name="`lignes[${index}][quantite]`" x-model="ligne.quantite"
                                   @input="calculerLigne(index)"
                                   @blur="appliquerCalcul(index, 'quantite', $event.target.value)"
                                   @keydown.enter.prevent="$event.target.blur()"
                                   :class="{ 'border-red-400 ring-red-200 ring-2': expressionErreur && expressionErreur.index === index && expressionErreur.champ === 'quantite' }"
                                   inputmode="decimal"
                                   title="Calcul autorisé : 12*2.5, (3+5)*2.4, 15-0.8, 25% …"
                                   class="w-full text-sm border-gray-300 rounded px-2 py-1.5 text-right">
                        </div>

                        {{-- Prix unitaire --}}
                        <div class="col-span-6 md:col-span-1">
                            <input type="text" :name="`lignes[${index}][prix_unitaire]`" x-model="ligne.prix_unitaire"
                                   @input="calculerLigne(index)"
                                   @blur="appliquerCalcul(index, 'prix_unitaire', $event.target.value)"
                                   @keydown.enter.prevent="$event.target.blur()"
                                   :class="{ 'border-red-400 ring-red-200 ring-2': expressionErreur && expressionErreur.index === index && expressionErreur.champ === 'prix_unitaire' }"
                                   inputmode="decimal"
                                   title="Calcul autorisé : 45*1.15, 60-12, 100*(1-15%) …"
                                   class="w-full text-sm border-gray-300 rounded px-2 py-1.5 text-right">
                        </div>

                        {{-- Remise --}}
                        <div class="col-span-6 md:col-span-2 flex gap-1">
                            <input type="text" :name="`lignes[${index}][remise_valeur]`" x-model="ligne.remise_valeur"
                                   @input="calculerLigne(index)"
                                   @blur="appliquerCalcul(index, 'remise_valeur', $event.target.value)"
                                   @keydown.enter.prevent="$event.target.blur()"
                                   :class="{ 'border-red-400 ring-red-200 ring-2': expressionErreur && expressionErreur.index === index && expressionErreur.champ === 'remise_valeur' }"
                                   inputmode="decimal"
                                   title="Calcul autorisé : 120*25%, 50+10, 30 …"
                                   class="w-full text-sm border-gray-300 rounded px-2 py-1.5 text-right">
                            <select :name="`lignes[${index}][remise_type]`" x-model="ligne.remise_type"
                                    @change="calculerLigne(index)"
                                    class="text-xs border-gray-300 rounded px-1">
                                <option value="montant">€</option>
                                <option value="pourcentage">%</option>
                            </select>
                        </div>

                        {{-- TVA --}}
                        <div class="col-span-6 md:col-span-1">
                            <select :name="`lignes[${index}][taux_tva]`" x-model.number="ligne.taux_tva"
                                    @change="calculerLigne(index)"
                                    class="w-full text-sm border-gray-300 rounded px-2 py-1.5">
                                @foreach($tauxTva as $t)
                                    <option value="{{ $t->taux }}">{{ number_format($t->taux, 0) }}%</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Montant HT --}}
                        <div class="col-span-6 md:col-span-1 flex items-center justify-end">
                            <span class="text-sm font-medium text-gray-700" x-text="formatMontant(ligne.montant_ht)"></span>
                        </div>

                        {{-- Actions --}}
                        <div class="col-span-6 md:col-span-1 flex items-center justify-end gap-1">
                            <div class="ligne-drag-handle text-gray-300 hover:text-gray-500 cursor-grab active:cursor-grabbing select-none p-0.5" title="Glisser pour réorganiser">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 012 2v12a2 2 0 01-4 0V4a2 2 0 012-2zM13 2a2 2 0 012 2v12a2 2 0 01-4 0V4a2 2 0 012-2z"/></svg>
                            </div>
                            <button type="button"
                                    @click="ouvrirHistorique(index)"
                                    x-show="ligne.produit_id || ligne.catalog_produit_id || (ligne.designation && ligne.designation.length >= 3)"
                                    class="text-gray-300 hover:text-indigo-500 p-0.5"
                                    title="Déjà vendu ? Voir l'historique des prix">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>
                            <button type="button" @click="deplacerHaut(index)" :disabled="index === 0"
                                    class="text-gray-300 hover:text-gray-500 disabled:opacity-20">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            </button>
                            <button type="button" @click="deplacerBas(index)" :disabled="index === lignes.length - 1"
                                    class="text-gray-300 hover:text-gray-500 disabled:opacity-20">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <button type="button" @click="supprimerLigne(index)" class="text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Détail (ligne secondaire) --}}
                    <div class="md:ml-0">
                        <textarea :name="`lignes[${index}][detail]`" x-model="ligne.detail"
                                  placeholder="Détails, description complémentaire… (les calculs Qté/Prix/Remise apparaissent ici)"
                                  rows="1"
                                  class="w-full text-xs border-gray-200 rounded px-2 py-1 text-gray-500 bg-gray-50 resize-none focus:ring-1 focus:ring-blue-300"></textarea>
                    </div>
                </div>
            </template>
        </div>
    </template>
    </div>{{-- /.lignes-sortable-container --}}

    {{-- Boutons ajout --}}
    <div class="flex gap-3 p-3 border-b border-gray-100">
        <button type="button" @click="ajouterLigne()"
                class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Ajouter une ligne
        </button>
        <button type="button" @click="ajouterSection()"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
            Ajouter une section
        </button>
        <div x-data="kitInsertion()" class="relative ml-auto flex items-center gap-3">
            <button type="button" @click="ouvrirModal()"
                    class="inline-flex items-center gap-1.5 text-sm text-green-600 hover:text-green-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Insérer un kit
            </button>
            <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/50" @click="modalOpen = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 flex flex-col max-h-[70vh]" @click.stop>
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">Insérer un kit</h3>
                        <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="px-6 py-3 border-b border-gray-100">
                        <input type="text" x-model="recherche" @input.debounce.300ms="chargerKits()"
                               placeholder="Rechercher un kit…"
                               class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div class="flex-1 overflow-y-auto px-6 py-3">
                        <template x-if="loading">
                            <p class="text-center text-sm text-gray-400 py-6">Chargement…</p>
                        </template>
                        <template x-if="!loading && kits.length === 0">
                            <div class="text-center py-6">
                                <p class="text-sm text-gray-400">Aucun kit trouvé.</p>
                                <a href="{{ route('kits.create') }}" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Créer votre premier kit →</a>
                            </div>
                        </template>
                        <template x-for="kit in kits" :key="kit.id">
                            <div @click="insererKit(kit)"
                                 class="flex items-center justify-between p-3 rounded-lg hover:bg-blue-50 cursor-pointer border border-transparent hover:border-blue-200 mb-2 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm text-gray-800" x-text="kit.nom"></div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        <span x-text="kit.nb_lignes + ' lignes'"></span>
                                        <span x-show="kit.categorie" class="ml-2">· <span x-text="kit.categorie"></span></span>
                                        <span x-show="kit.nb_utilisations > 0" class="ml-2">· utilisé <span x-text="kit.nb_utilisations"></span>×</span>
                                    </div>
                                    <div x-show="kit.description" class="text-xs text-gray-400 mt-1 truncate" x-text="kit.description"></div>
                                </div>
                                <div class="text-right ml-3 flex-shrink-0">
                                    <div class="text-sm font-medium text-gray-700" x-text="(kit.estimation_ht || 0).toFixed(2) + ' € HT'"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" @click="ouvrirCatalogue()"
                class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Tarifs fournisseurs
        </button>
    </div>

    {{-- Modal catalogue fournisseurs --}}
    <div x-show="catalogOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="catalogOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-3xl mx-4 flex flex-col max-h-[80vh]" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Tarifs fournisseurs</h3>
                <button @click="catalogOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-3 border-b border-gray-100 flex gap-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="catalogQ"
                           @input.debounce.300ms="rechercherCatalogue()"
                           placeholder="Référence, désignation, marque…"
                           class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <select x-model="catalogFournisseur" @change="rechercherCatalogue()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 min-w-36">
                    <option value="">Tous</option>
                    <option value="desco">Desco</option>
                    <option value="vanmarke">VanMarke</option>
                    <option value="wasco">Wasco</option>
                    <option value="ems">EMS</option>
                </select>
            </div>
            <div class="overflow-y-auto flex-1">
                <template x-if="catalogLoading">
                    <div class="flex justify-center items-center py-12">
                        <svg class="animate-spin w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </div>
                </template>
                <template x-if="!catalogLoading && catalogResults.length === 0 && catalogQ.length >= 2">
                    <div class="text-center py-12 text-gray-400 text-sm">Aucun produit trouvé pour « <span x-text="catalogQ"></span> »</div>
                </template>
                <template x-if="!catalogLoading && catalogQ.length < 2">
                    <div class="text-center py-12 text-gray-400 text-sm">Saisissez au moins 2 caractères pour rechercher</div>
                </template>
                <template x-if="!catalogLoading && catalogResults.length > 0">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                                <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 uppercase">Référence</th>
                                <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 uppercase">Désignation</th>
                                <th class="text-right px-4 py-2 text-xs font-medium text-gray-500 uppercase">Prix</th>
                                <th class="text-center px-4 py-2 text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(p, i) in catalogResults" :key="i">
                                <tr class="hover:bg-blue-50 cursor-pointer" @click="ajouterDepuisCatalogue(p)">
                                    <td class="px-4 py-2.5">
                                        <span class="text-xs font-medium text-gray-600" x-text="p.fournisseur"></span>
                                    </td>
                                    <td class="px-4 py-2.5 font-mono text-xs text-gray-500" x-text="p.reference"></td>
                                    <td class="px-4 py-2.5 font-medium text-gray-900">
                                        <span x-text="p.designation"></span>
                                        <template x-if="p.nb_equivalents > 1">
                                            <span class="ml-2 inline-block px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700"
                                                  :title="`Disponible chez ${p.nb_equivalents} fournisseurs`">
                                                📊 <span x-text="p.nb_equivalents"></span> fourn.
                                            </span>
                                        </template>
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        <span class="font-semibold" x-text="p.prix.toFixed(2) + ' €'"></span>
                                        <span class="block text-xs text-gray-400" x-text="'TVA ' + p.taux_tva + '% / ' + p.unite"></span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span :class="p.en_stock ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                                              class="inline-block px-2 py-0.5 text-xs rounded-full"
                                              x-text="p.en_stock ? 'Dispo' : 'Rupture'"></span>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <button type="button" @click.stop="ajouterDepuisCatalogue(p)"
                                                class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            Ajouter
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </template>
            </div>
        </div>
    </div>

    {{-- Pop-over historique des ventes --}}
    <div x-show="historiqueOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="historiqueOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 flex flex-col max-h-[85vh]" @click.stop>

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900">Déjà vendu à ce client</h3>
                    <p class="text-xs text-gray-500 mt-0.5 truncate"
                       x-text="historiqueLigneIndex !== null ? (lignes[historiqueLigneIndex]?.designation || '') : ''"></p>
                </div>
                <button @click="historiqueOpen = false" class="text-gray-400 hover:text-gray-600 ml-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Bandeau prix catalogue actuel --}}
            <template x-if="historiqueData && historiqueData.prix_catalogue_actuel !== null && historiqueData.prix_catalogue_actuel !== undefined">
                <div class="px-6 py-2.5 bg-gray-50 border-b border-gray-100 text-xs text-gray-600">
                    💶 Prix catalogue actuel :
                    <span class="font-semibold text-gray-900" x-text="historiqueData.prix_catalogue_actuel.toFixed(2) + ' €'"></span>
                </div>
            </template>

            {{-- Loading --}}
            <div x-show="historiqueLoading" class="flex items-center justify-center py-12 text-sm text-gray-400">
                Chargement…
            </div>

            {{-- Contenu --}}
            <div x-show="!historiqueLoading && historiqueData" class="overflow-y-auto flex-1">

                {{-- Stats ce client --}}
                <template x-if="clientId && historiqueData && historiqueData.stats_ce_client && historiqueData.stats_ce_client.nb > 0">
                    <div class="px-6 py-4 bg-blue-50 border-b border-blue-100">
                        <div class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-2">
                            À ce client (<span x-text="historiqueData.stats_ce_client.nb"></span> vente<span x-text="historiqueData.stats_ce_client.nb > 1 ? 's' : ''"></span>)
                        </div>
                        <div class="grid grid-cols-4 gap-2 text-sm">
                            <div>
                                <div class="text-xs text-gray-500">Prix min</div>
                                <div class="font-semibold" x-text="historiqueData.stats_ce_client.prix_min.toFixed(2) + ' €'"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Prix moyen</div>
                                <div class="font-semibold text-blue-700" x-text="historiqueData.stats_ce_client.prix_moy.toFixed(2) + ' €'"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Prix max</div>
                                <div class="font-semibold" x-text="historiqueData.stats_ce_client.prix_max.toFixed(2) + ' €'"></div>
                            </div>
                            <div x-show="historiqueData.stats_ce_client.marge_moy_pct !== null">
                                <div class="text-xs text-gray-500">Marge moy.</div>
                                <div class="font-semibold text-indigo-700"
                                     x-text="(historiqueData.stats_ce_client.marge_moy_pct > 0 ? '+' : '') + historiqueData.stats_ce_client.marge_moy_pct.toFixed(1) + '%'"></div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Ventes ce client --}}
                <template x-if="historiqueData && historiqueData.ventes_ce_client && historiqueData.ventes_ce_client.length > 0">
                    <div class="px-6 py-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Dernières ventes à ce client</h4>
                        <div class="space-y-3">
                            <template x-for="v in historiqueData.ventes_ce_client" :key="v.ligne_id">
                                <div class="bg-gray-50 rounded-lg p-3 text-sm">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-block px-2 py-0.5 text-xs rounded-full"
                                              :class="v.document_type === 'facture' ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700'"
                                              x-text="v.document_type === 'facture' ? 'FAC' : 'BDC'"></span>
                                        <span class="font-mono text-xs text-gray-500" x-text="v.document_numero"></span>
                                        <span class="text-xs text-gray-400"
                                              x-text="new Date(v.date_document).toLocaleDateString('fr-BE') + ' · il y a ' + v.age_mois + ' mois'"></span>
                                        <span x-show="v.chantier_nom" class="text-xs text-gray-500 ml-auto truncate" x-text="v.chantier_nom || ''"></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-3 flex-wrap">
                                                <div>
                                                    <div class="text-xs text-gray-500">Prix d'alors</div>
                                                    <div class="font-semibold text-gray-900" x-text="v.prix_unitaire.toFixed(2) + ' €'"></div>
                                                </div>
                                                <template x-if="v.contexte_marge">
                                                    <div class="flex items-center gap-3 border-l border-gray-300 pl-3">
                                                        <div>
                                                            <div class="text-xs text-gray-500">Marge d'alors</div>
                                                            <div class="font-semibold text-indigo-700"
                                                                 x-text="(v.contexte_marge.marge_pct_epoque > 0 ? '+' : '') + v.contexte_marge.marge_pct_epoque.toFixed(1) + '%'"></div>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-500">Catalogue</div>
                                                            <div class="text-xs font-mono">
                                                                <span x-text="v.contexte_marge.prix_catalogue_epoque.toFixed(2)"></span>
                                                                <span class="text-gray-400">→</span>
                                                                <span :class="classeEvolution(v.contexte_marge.evolution_catalogue_pct)"
                                                                      class="font-semibold"
                                                                      x-text="v.contexte_marge.prix_catalogue_actuel.toFixed(2) + ' (' + (v.contexte_marge.evolution_catalogue_pct > 0 ? '+' : '') + v.contexte_marge.evolution_catalogue_pct.toFixed(1) + '%)'"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-1 flex-shrink-0">
                                            <template x-if="v.contexte_marge">
                                                <button @click="reprendreMarge(v.contexte_marge.prix_equivalent_actuel)"
                                                        class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 whitespace-nowrap">
                                                    Reprendre marge
                                                    <span class="font-semibold ml-0.5" x-text="'(' + v.contexte_marge.prix_equivalent_actuel.toFixed(2) + ' €)'"></span>
                                                </button>
                                            </template>
                                            <button @click="reprendrePrixBrut(v.prix_unitaire)"
                                                    class="text-xs px-3 py-1.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 whitespace-nowrap"
                                                    x-text="v.contexte_marge ? 'Prix brut (' + v.prix_unitaire.toFixed(2) + ' €)' : 'Reprendre (' + v.prix_unitaire.toFixed(2) + ' €)'">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Aucune vente à ce client --}}
                <template x-if="clientId && historiqueData && (!historiqueData.ventes_ce_client || historiqueData.ventes_ce_client.length === 0)">
                    <div class="px-6 py-4 text-sm text-gray-500 italic border-b border-gray-100">
                        Ce produit n'a jamais été vendu à ce client.
                    </div>
                </template>

                {{-- Ventes autres clients --}}
                <template x-if="historiqueData && historiqueData.ventes_autres_clients && historiqueData.ventes_autres_clients.length > 0">
                    <div class="px-6 py-4 border-t border-gray-100">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">
                            <span x-show="clientId">À d'autres clients (référence)</span>
                            <span x-show="!clientId">Dernières ventes</span>
                        </h4>
                        <div class="space-y-2">
                            <template x-for="v in historiqueData.ventes_autres_clients" :key="v.ligne_id">
                                <div class="bg-white border border-gray-100 rounded-lg px-3 py-2 text-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-gray-800 truncate" x-text="v.client_nom || '—'"></div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                <span x-text="v.document_numero"></span> ·
                                                <span x-text="new Date(v.date_document).toLocaleDateString('fr-BE')"></span>
                                                <template x-if="v.contexte_marge">
                                                    <span class="ml-1 text-indigo-600"
                                                          x-text="'marge ' + (v.contexte_marge.marge_pct_epoque > 0 ? '+' : '') + v.contexte_marge.marge_pct_epoque.toFixed(1) + '%'"></span>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <div class="font-semibold text-gray-700" x-text="v.prix_unitaire.toFixed(2) + ' €'"></div>
                                            <template x-if="v.contexte_marge">
                                                <div class="text-xs text-gray-400"
                                                     x-text="'équiv. ' + v.contexte_marge.prix_equivalent_actuel.toFixed(2) + ' €'"></div>
                                            </template>
                                        </div>
                                        <div class="flex flex-col gap-1 flex-shrink-0">
                                            <template x-if="v.contexte_marge">
                                                <button @click="reprendreMarge(v.contexte_marge.prix_equivalent_actuel)"
                                                        class="text-xs px-2 py-1 border border-blue-300 text-blue-700 rounded hover:bg-blue-50 whitespace-nowrap">
                                                    Marge
                                                </button>
                                            </template>
                                            <button @click="reprendrePrixBrut(v.prix_unitaire)"
                                                    class="text-xs px-2 py-1 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 whitespace-nowrap">
                                                Brut
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Aucun résultat --}}
                <template x-if="historiqueData && (!historiqueData.ventes_ce_client || historiqueData.ventes_ce_client.length === 0) && (!historiqueData.ventes_autres_clients || historiqueData.ventes_autres_clients.length === 0)">
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">
                        Aucune vente antérieure pour ce produit sur les 24 derniers mois.
                    </div>
                </template>

            </div>
        </div>
    </div>

    {{-- Totaux --}}
    <div class="p-4 bg-gray-50 rounded-b-xl">
        <div class="flex justify-end">
            <div class="w-72 space-y-1 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Total HT</span>
                    <span x-text="formatMontant(totalHt)"></span>
                </div>
                <template x-for="[taux, montant] in Object.entries(totauxTva)" :key="taux">
                    <div class="flex justify-between text-gray-500">
                        <span x-text="`TVA ${taux}%`"></span>
                        <span x-text="formatMontant(montant)"></span>
                    </div>
                </template>
                <div class="flex justify-between font-semibold text-gray-900 border-t border-gray-300 pt-1 mt-1">
                    <span>Total TTC</span>
                    <span x-text="formatMontant(totalTtc)"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (hover: hover) and (pointer: fine) {
    .ligne-drag-handle { opacity: 0; transition: opacity 150ms; }
    .lignes-sortable-container > div:hover .ligne-drag-handle { opacity: 1; }
}
@media (hover: none), (pointer: coarse) {
    .ligne-drag-handle { opacity: 1; }
}
.sortable-ghost   { opacity: 0.4; background-color: rgb(239 246 255); }
.sortable-drag    { opacity: 0.9; box-shadow: 0 10px 25px -5px rgba(0,0,0,.1); cursor: grabbing; }
.sortable-chosen  { background-color: rgb(239 246 255); }
</style>

<script>
function lignesDocument(lignesInitiales, tvaDefaut, clientIdInitial) {
    return {
        lignes: lignesInitiales.length > 0
            ? lignesInitiales.map(l => ({...l, _uid: crypto.randomUUID(), montant_ht: parseFloat(l.montant_ht) || 0,
                produit_id: l.produit_id || null,
                catalog_produit_id: l.catalog_produit_id || null,
                produit_key: l.produit_key || '' }))
            : [{ _uid: crypto.randomUUID(), designation: '', detail: '', unite: 'pièce', quantite: 1, prix_unitaire: 0, remise_valeur: 0, remise_type: 'montant', taux_tva: tvaDefaut, est_section: false, montant_ht: 0, produit_id: null, catalog_produit_id: null, produit_key: '' }],

        // Client actif (pour historique prix)
        clientId: clientIdInitial,

        // Historique des ventes
        historiqueOpen: false,
        historiqueLigneIndex: null,
        historiqueData: null,
        historiqueLoading: false,

        // Flash erreur expression arithmétique (réinitialisé après 2s)
        expressionErreur: null,

        // Catalogue fournisseurs
        catalogOpen: false,
        catalogQ: '',
        catalogFournisseur: '',
        catalogResults: [],
        catalogLoading: false,

        get totalHt() {
            return this.lignes.filter(l => !l.est_section).reduce((s, l) => s + l.montant_ht, 0);
        },

        get totauxTva() {
            const t = {};
            this.lignes.filter(l => !l.est_section).forEach(l => {
                const taux = parseFloat(l.taux_tva) || 0;
                const k = taux.toFixed(2);
                t[k] = (t[k] || 0) + l.montant_ht * (taux / 100);
            });
            return t;
        },

        get totalTtc() {
            return this.totalHt + Object.values(this.totauxTva).reduce((s, v) => s + v, 0);
        },

        calculerLigne(index) {
            const l = this.lignes[index];
            if (l.est_section) return;
            const brut = (parseFloat(l.prix_unitaire) || 0) * (parseFloat(l.quantite) || 0);
            const remise = l.remise_type === 'pourcentage'
                ? brut * ((parseFloat(l.remise_valeur) || 0) / 100)
                : (parseFloat(l.remise_valeur) || 0);
            l.montant_ht = Math.max(0, brut - remise);
        },

        nouvelleLigneVide() {
            return { _uid: crypto.randomUUID(), designation: '', detail: '', unite: 'pièce', quantite: 1, prix_unitaire: 0, remise_valeur: 0, remise_type: 'montant', taux_tva: tvaDefaut, est_section: false, montant_ht: 0, produit_id: null, catalog_produit_id: null, produit_key: '' };
        },

        ajouterLigne() {
            this.lignes.push(this.nouvelleLigneVide());
        },

        ajouterSection() {
            this.lignes.push({ _uid: crypto.randomUUID(), designation: '', detail: '', unite: '—', quantite: 0, prix_unitaire: 0, remise_valeur: 0, remise_type: 'montant', taux_tva: tvaDefaut, est_section: true, montant_ht: 0, produit_id: null, catalog_produit_id: null, produit_key: '' });
        },

        supprimerLigne(index) {
            if (this.lignes.length <= 1) return;
            this.lignes.splice(index, 1);
        },

        deplacerHaut(index) {
            if (index === 0) return;
            [this.lignes[index - 1], this.lignes[index]] = [this.lignes[index], this.lignes[index - 1]];
        },

        deplacerBas(index) {
            if (index >= this.lignes.length - 1) return;
            [this.lignes[index + 1], this.lignes[index]] = [this.lignes[index], this.lignes[index + 1]];
        },

        async rechercherProduit(q, index, callback) {
            // Collecter les clés des produits déjà dans le document
            const produitsActuels = this.lignes
                .filter(l => l.produit_key)
                .map(l => l.produit_key);

            let url = '{{ route("produits.suggestions") }}';
            const params = [];
            if (q && q.length >= 2) params.push('q=' + encodeURIComponent(q));
            produitsActuels.forEach(p => params.push('produits[]=' + encodeURIComponent(p)));
            if (params.length) url += '?' + params.join('&');

            try {
                const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                callback(await r.json());
            } catch (e) {
                callback([]);
            }
        },

        selectionnerProduit(index, produit) {
            const l = this.lignes[index];
            const coeff = window.coefficientMargeActuel || 0;

            l.designation        = produit.designation;
            l.detail             = produit.description || (produit.reference ? `Réf. ${produit.reference}${produit.fournisseur ? ' — ' + produit.fournisseur : ''}` : '');
            l.unite              = produit.unite || 'pièce';
            l.taux_tva           = parseFloat(produit.taux_tva) || tvaDefaut;

            if (produit.source === 'catalogue') {
                const prixBase = parseFloat(produit.prix_base) || parseFloat(produit.prix) || 0;
                l.prix_unitaire      = coeff > 0 && prixBase > 0 ? Math.round(prixBase * (1 + coeff / 100) * 100) / 100 : parseFloat(produit.prix) || 0;
                l.catalog_produit_id = produit.id;
                l.produit_id         = null;
                l.produit_key        = 'c:' + produit.id;
            } else {
                l.prix_unitaire      = parseFloat(produit.prix_unitaire || produit.prix) || 0;
                l.produit_id         = produit.id;
                l.catalog_produit_id = null;
                l.produit_key        = 'p:' + produit.id;
            }

            this.calculerLigne(index);
        },

        ouvrirCatalogue() {
            this.catalogOpen = true;
            this.catalogQ = '';
            this.catalogResults = [];
        },

        async rechercherCatalogue() {
            if (this.catalogQ.length < 2) { this.catalogResults = []; return; }
            this.catalogLoading = true;
            const url = `/api/catalog/search?q=${encodeURIComponent(this.catalogQ)}`
                      + (this.catalogFournisseur ? `&fournisseur=${this.catalogFournisseur}` : '');
            const r = await fetch(url);
            this.catalogResults = await r.json();
            this.catalogLoading = false;
        },

        appliquerMarge(prixBase, coeff) {
            if (!coeff || coeff <= 0) return prixBase;
            return Math.round(prixBase * (1 + coeff / 100) * 100) / 100;
        },

        ajouterDepuisCatalogue(produit) {
            const coeff  = window.coefficientMargeActuel || 0;
            const prix   = coeff > 0 && produit.prix_base > 0
                ? this.appliquerMarge(parseFloat(produit.prix_base), coeff)
                : parseFloat(produit.prix) || 0;

            this.lignes.push({
                _uid:               crypto.randomUUID(),
                designation:        produit.designation,
                detail:             produit.reference ? `Réf. ${produit.reference} — ${produit.fournisseur}` : '',
                unite:              produit.unite,
                quantite:           1,
                prix_unitaire:      prix,
                remise_valeur:      0,
                remise_type:        'montant',
                taux_tva:           parseFloat(produit.taux_tva) || tvaDefaut,
                est_section:        false,
                montant_ht:         prix,
                produit_id:         null,
                catalog_produit_id: produit.id,
                produit_key:        'c:' + produit.id,
            });
            this.catalogOpen = false;
        },

        init() {
            // Restauration depuis l'auto-save
            window.addEventListener('restaurer-lignes', (e) => {
                const lignes = e.detail.lignes;
                if (!lignes || lignes.length === 0) return;
                this.lignes = lignes.map(l => ({
                    _uid:               crypto.randomUUID(),
                    designation:        l.designation || '',
                    detail:             l.detail || '',
                    unite:              l.unite || 'pièce',
                    quantite:           parseFloat(l.quantite) || 0,
                    prix_unitaire:      parseFloat(l.prix_unitaire) || 0,
                    remise_valeur:      parseFloat(l.remise_valeur) || 0,
                    remise_type:        l.remise_type || 'montant',
                    taux_tva:           parseFloat(l.taux_tva) || tvaDefaut,
                    est_section:        l.est_section === '1' || l.est_section === true,
                    montant_ht:         parseFloat(l.montant_ht) || 0,
                    produit_id:         l.produit_id ? parseInt(l.produit_id) : null,
                    catalog_produit_id: l.catalog_produit_id ? parseInt(l.catalog_produit_id) : null,
                    produit_key:        l.produit_key || '',
                }));
                this.lignes.forEach((_, i) => this.calculerLigne(i));
            });

            // Écouter un kit complet (toutes ses lignes en une seule fois)
            window.addEventListener('inserer-kit', (e) => {
                const kitLignes = e.detail.lignes;

                // Supprimer la ligne vide initiale si le document n'a qu'une seule ligne vide
                if (this.lignes.length === 1) {
                    const l = this.lignes[0];
                    if (!l.est_section && !l.designation && !l.prix_unitaire) {
                        this.lignes.splice(0, 1);
                    }
                }

                kitLignes.forEach(l => {
                    this.lignes.push({
                        _uid:               crypto.randomUUID(),
                        designation:        l.designation,
                        detail:             l.detail || '',
                        unite:              l.unite || (l.est_section ? '—' : 'pièce'),
                        quantite:           l.est_section ? 0 : (parseFloat(l.quantite) || 1),
                        prix_unitaire:      l.est_section ? 0 : (parseFloat(l.prix_unitaire) || 0),
                        remise_valeur:      l.est_section ? 0 : (parseFloat(l.remise_valeur) || 0),
                        remise_type:        l.remise_type || 'montant',
                        taux_tva:           parseFloat(l.taux_tva) || tvaDefaut,
                        est_section:        !!l.est_section,
                        montant_ht:         l.est_section ? 0 : (parseFloat(l.montant_ht) || 0),
                        produit_id:         l.produit_id || null,
                        catalog_produit_id: l.catalog_produit_id || null,
                        produit_key:        l.produit_key || '',
                    });
                    if (!l.est_section) {
                        this.calculerLigne(this.lignes.length - 1);
                    }
                });
            });

            // Suivre le changement de client (combobox)
            window.addEventListener('combobox-selected', (e) => {
                if (e.detail.field === 'client_id') this.clientId = e.detail.id;
            });
            window.addEventListener('combobox-cleared', (e) => {
                if (e.detail.field === 'client_id') this.clientId = null;
            });

            // Forcer l'évaluation des expressions avant soumission du formulaire
            const form = this.$root.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    if (document.activeElement && document.activeElement.tagName === 'INPUT') {
                        document.activeElement.blur();
                    }
                });
            }

            // Initialiser le drag & drop SortableJS
            this.$nextTick(() => {
                const container = this.$refs.lignesSortable;
                if (!container || typeof Sortable === 'undefined') return;

                Sortable.create(container, {
                    handle: '.ligne-drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass:   'sortable-drag',
                    filter: 'input, textarea, select, button',
                    preventOnFilter: false,

                    onEnd: (evt) => {
                        const { oldIndex, newIndex } = evt;
                        if (oldIndex === newIndex) return;
                        const ligneDeplacee = this.lignes.splice(oldIndex, 1)[0];
                        this.lignes.splice(newIndex, 0, ligneDeplacee);
                        // Notifier l'auto-save qu'il y a eu un changement
                        const f = this.$root.closest('form');
                        if (f) f.dispatchEvent(new Event('input', { bubbles: true }));
                    },
                });
            });

            // Écouter les suggestions cliquées depuis le bandeau "Produits habituels"
            window.addEventListener('ajouter-produit', (e) => {
                const p = e.detail;
                const coeff = window.coefficientMargeActuel || 0;
                const prixBase = parseFloat(p.prix_base) || 0;
                const prix = p.source === 'catalogue' && coeff > 0 && prixBase > 0
                    ? Math.round(prixBase * (1 + coeff / 100) * 100) / 100
                    : parseFloat(p.prix || p.prix_unitaire) || 0;

                this.lignes.push({
                    _uid:               crypto.randomUUID(),
                    designation:        p.designation,
                    detail:             p.reference ? `Réf. ${p.reference}${p.fournisseur ? ' — ' + p.fournisseur : ''}` : '',
                    unite:              p.unite || 'pièce',
                    quantite:           1,
                    prix_unitaire:      prix,
                    remise_valeur:      0,
                    remise_type:        'montant',
                    taux_tva:           parseFloat(p.taux_tva) || tvaDefaut,
                    est_section:        false,
                    montant_ht:         prix,
                    produit_id:         p.source === 'interne' ? p.id : null,
                    catalog_produit_id: p.source === 'catalogue' ? p.id : null,
                    produit_key:        (p.source === 'interne' ? 'p:' : 'c:') + p.id,
                });
                this.calculerLigne(this.lignes.length - 1);
            });
        },

        async ouvrirHistorique(index) {
            const l = this.lignes[index];
            if (!l.produit_id && !l.catalog_produit_id && (!l.designation || l.designation.length < 3)) return;

            this.historiqueLigneIndex = index;
            this.historiqueOpen       = true;
            this.historiqueLoading    = true;
            this.historiqueData       = null;

            const params = new URLSearchParams();
            if (l.produit_id)         params.set('produit_id', l.produit_id);
            if (l.catalog_produit_id) params.set('catalog_produit_id', l.catalog_produit_id);
            if (!l.produit_id && !l.catalog_produit_id) params.set('designation', l.designation);
            if (this.clientId) params.set('client_id', this.clientId);

            try {
                const r = await fetch(`{{ route('ventes.historique') }}?` + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.historiqueData = await r.json();
            } catch (e) {
                this.historiqueData = { ventes_ce_client: [], ventes_autres_clients: [], stats_ce_client: { nb: 0 }, stats_toutes: { nb: 0 }, prix_catalogue_actuel: null };
            } finally {
                this.historiqueLoading = false;
            }
        },

        reprendreMarge(prixEquivalent) {
            if (this.historiqueLigneIndex === null) return;
            this.lignes[this.historiqueLigneIndex].prix_unitaire = parseFloat(prixEquivalent);
            this.calculerLigne(this.historiqueLigneIndex);
            this.historiqueOpen = false;
        },

        reprendrePrixBrut(prix) {
            if (this.historiqueLigneIndex === null) return;
            this.lignes[this.historiqueLigneIndex].prix_unitaire = parseFloat(prix);
            this.calculerLigne(this.historiqueLigneIndex);
            this.historiqueOpen = false;
        },

        classeEvolution(evolutionPct) {
            if (evolutionPct === null || evolutionPct === undefined) return '';
            const abs = Math.abs(evolutionPct);
            if (abs < 3)  return 'text-green-600';
            if (abs < 10) return 'text-amber-600';
            return 'text-red-600';
        },

        // ----- Calculatrice intégrée -----

        evaluerExpression(input) {
            if (input === null || input === undefined) return null;
            if (typeof input === 'number') return isFinite(input) ? input : null;
            const str = String(input).trim();
            if (str === '') return null;
            let expr = str.replace(/,/g, '.');
            if (!/^[\d+\-*/%().\s]+$/.test(expr)) return null;
            // "25%" standalone → "(25/100)", "X % Y" avec espaces reste modulo
            expr = expr.replace(/(\d+(?:\.\d+)?)\s*%(?!\s*\d)/g, '($1/100)');
            if (/^-?\d+(?:\.\d+)?$/.test(expr.trim())) {
                const n = parseFloat(expr);
                return isFinite(n) ? n : null;
            }
            try {
                // eslint-disable-next-line no-new-func
                const result = new Function('return (' + expr + ')')();
                if (typeof result !== 'number' || !isFinite(result)) return null;
                return Math.round(result * 10000) / 10000;
            } catch (e) {
                return null;
            }
        },

        estExpression(input) {
            if (typeof input !== 'string') return false;
            return /[+\-*/%()]/.test(input.replace(/^-/, ''));
        },

        formatNombreSimple(n) {
            if (Number.isInteger(n)) return String(n);
            return parseFloat(n.toFixed(4)).toString();
        },

        appliquerCalcul(index, champ, raw) {
            const ligne = this.lignes[index];
            if (!ligne || ligne.est_section) return;
            const rawStr = String(raw || '').trim();
            if (rawStr === '' || !this.estExpression(rawStr)) {
                this.calculerLigne(index);
                return;
            }
            const resultat = this.evaluerExpression(rawStr);
            if (resultat === null) {
                this.expressionErreur = { index, champ };
                setTimeout(() => {
                    if (this.expressionErreur && this.expressionErreur.index === index && this.expressionErreur.champ === champ) {
                        this.expressionErreur = null;
                    }
                }, 2000);
                return;
            }
            const libellesChamp = { quantite: 'Qté', prix_unitaire: 'Prix', remise_valeur: 'Remise' };
            const libelle = libellesChamp[champ] || champ;
            const exprFormatee = rawStr.replace(/\s+/g, ' ').trim();
            ligne[champ] = resultat;
            const traceCalcul = `${libelle} : ${exprFormatee} = ${this.formatNombreSimple(resultat)}`;
            const detailActuel = (ligne.detail || '').trim();
            const regexTraceExistante = new RegExp(`${libelle} : [^\\n]*(\\n|$)`, 'g');
            const detailNettoye = detailActuel.replace(regexTraceExistante, '').trim();
            ligne.detail = detailNettoye ? `${detailNettoye}\n${traceCalcul}` : traceCalcul;
            this.calculerLigne(index);
        },

        formatMontant(v) {
            return new Intl.NumberFormat('fr-BE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v || 0) + ' €';
        },
    }
}

function kitInsertion() {
    return {
        modalOpen: false,
        recherche: '',
        kits: [],
        loading: false,

        ouvrirModal() {
            this.modalOpen = true;
            this.chargerKits();
        },

        async chargerKits() {
            this.loading = true;
            try {
                let url = '{{ route("kits.api-list") }}';
                if (this.recherche.length >= 2) {
                    url += '?q=' + encodeURIComponent(this.recherche);
                }
                const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                this.kits = await r.json();
            } catch (e) {
                this.kits = [];
            }
            this.loading = false;
        },

        async insererKit(kit) {
            try {
                const r = await fetch('{{ url("/api/kits") }}/' + kit.id + '/lignes', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const lignes = await r.json();
                const coeff = window.coefficientMargeActuel || 0;

                const lignesPreparees = lignes.map(ligne => {
                    const l = { ...ligne };
                    if (coeff > 0 && l.prix_unitaire > 0 && !l.est_section) {
                        l.prix_unitaire = Math.round(l.prix_unitaire * (1 + coeff / 100) * 100) / 100;
                    }
                    if (!l.est_section) {
                        const brut = l.prix_unitaire * l.quantite;
                        const remise = l.remise_type === 'pourcentage'
                            ? brut * (l.remise_valeur / 100)
                            : l.remise_valeur;
                        l.montant_ht = Math.max(0, brut - remise);
                    }
                    return l;
                });

                window.dispatchEvent(new CustomEvent('inserer-kit', { detail: { lignes: lignesPreparees } }));
                this.modalOpen = false;
            } catch (e) {
                console.error('Erreur insertion kit:', e);
            }
        },
    };
}
</script>
