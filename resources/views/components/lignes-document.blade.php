{{--
    Composant réutilisable pour la saisie des lignes d'un document.
    Props :
      $lignesInitiales : collection de LigneDocument (pour l'édition)
      $tauxTva : collection de TauxTva
      $tvaDefaut : taux par défaut (ex: 21)
--}}
@props(['lignesInitiales' => collect(), 'tauxTva', 'tvaDefaut' => 21])

<div x-data="lignesDocument({{ json_encode($lignesInitiales->map(fn($l) => [
    'designation'   => $l->designation,
    'detail'        => $l->detail ?? '',
    'unite'         => $l->unite,
    'quantite'      => $l->quantite,
    'prix_unitaire' => $l->prix_unitaire,
    'remise_valeur' => $l->remise_valeur,
    'remise_type'   => $l->remise_type,
    'taux_tva'      => $l->taux_tva,
    'est_section'   => $l->est_section,
    'montant_ht'    => $l->montant_ht,
])->values()) }}, {{ $tvaDefaut }})">

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
    <template x-for="(ligne, index) in lignes" :key="index">
        <div class="border-b border-gray-100 hover:bg-gray-50" :class="ligne.est_section ? 'bg-blue-50' : ''">

            {{-- Ligne titre/section --}}
            <template x-if="ligne.est_section">
                <div class="flex items-center gap-2 px-3 py-2">
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
                    <button type="button" @click="supprimerLigne(index)" class="text-red-400 hover:text-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>

            {{-- Ligne produit normale --}}
            <template x-if="!ligne.est_section">
                <div class="p-3 space-y-2">
                    <div class="grid grid-cols-12 gap-2 items-start">
                        {{-- Désignation + autocomplete --}}
                        <div class="col-span-12 md:col-span-4 relative" x-data="{ suggestions: [], loading: false }">
                            <input :name="`lignes[${index}][designation]`"
                                   x-model="ligne.designation"
                                   @input.debounce.300ms="rechercherProduit($event.target.value, suggestions => { $data.suggestions = suggestions })"
                                   placeholder="Désignation du produit…"
                                   class="w-full text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 px-2 py-1.5"
                                   autocomplete="off">
                            <div x-show="suggestions.length > 0"
                                 class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg text-sm max-h-48 overflow-y-auto">
                                <template x-for="s in suggestions">
                                    <div @click="selectionnerProduit(index, s); suggestions = []"
                                         class="px-3 py-2 hover:bg-blue-50 cursor-pointer">
                                        <div class="font-medium" x-text="s.designation"></div>
                                        <div class="text-xs text-gray-400" x-text="`${s.prix_unitaire} € / ${s.unite} — TVA ${s.taux_tva}%`"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Unité --}}
                        <div class="col-span-6 md:col-span-1">
                            <input :name="`lignes[${index}][unite]`" x-model="ligne.unite"
                                   placeholder="pièce" class="w-full text-sm border-gray-300 rounded px-2 py-1.5 text-center">
                        </div>

                        {{-- Quantité --}}
                        <div class="col-span-6 md:col-span-1">
                            <input type="number" :name="`lignes[${index}][quantite]`" x-model.number="ligne.quantite"
                                   step="0.01" min="0" @input="calculerLigne(index)"
                                   class="w-full text-sm border-gray-300 rounded px-2 py-1.5 text-right">
                        </div>

                        {{-- Prix unitaire --}}
                        <div class="col-span-6 md:col-span-1">
                            <input type="number" :name="`lignes[${index}][prix_unitaire]`" x-model.number="ligne.prix_unitaire"
                                   step="0.0001" min="0" @input="calculerLigne(index)"
                                   class="w-full text-sm border-gray-300 rounded px-2 py-1.5 text-right">
                        </div>

                        {{-- Remise --}}
                        <div class="col-span-6 md:col-span-2 flex gap-1">
                            <input type="number" :name="`lignes[${index}][remise_valeur]`" x-model.number="ligne.remise_valeur"
                                   step="0.01" min="0" @input="calculerLigne(index)"
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
                                  placeholder="Détails, description complémentaire…"
                                  rows="1"
                                  class="w-full text-xs border-gray-200 rounded px-2 py-1 text-gray-500 bg-gray-50 resize-none focus:ring-1 focus:ring-blue-300"></textarea>
                    </div>
                </div>
            </template>
        </div>
    </template>

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
        <button type="button" @click="ouvrirCatalogue()"
                class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-700 ml-auto">
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
                                    <td class="px-4 py-2.5 font-medium text-gray-900" x-text="p.designation"></td>
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

<script>
function lignesDocument(lignesInitiales, tvaDefaut) {
    return {
        lignes: lignesInitiales.length > 0
            ? lignesInitiales.map(l => ({...l, montant_ht: parseFloat(l.montant_ht) || 0}))
            : [{ designation: '', detail: '', unite: 'pièce', quantite: 1, prix_unitaire: 0, remise_valeur: 0, remise_type: 'montant', taux_tva: tvaDefaut, est_section: false, montant_ht: 0 }],

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

        ajouterLigne() {
            this.lignes.push({ designation: '', detail: '', unite: 'pièce', quantite: 1, prix_unitaire: 0, remise_valeur: 0, remise_type: 'montant', taux_tva: tvaDefaut, est_section: false, montant_ht: 0 });
        },

        ajouterSection() {
            this.lignes.push({ designation: '', detail: '', unite: '—', quantite: 0, prix_unitaire: 0, remise_valeur: 0, remise_type: 'montant', taux_tva: tvaDefaut, est_section: true, montant_ht: 0 });
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

        async rechercherProduit(q, callback) {
            if (q.length < 2) { callback([]); return; }
            const r = await fetch(`/api/produits/search?q=${encodeURIComponent(q)}`);
            callback(await r.json());
        },

        selectionnerProduit(index, produit) {
            const l = this.lignes[index];
            l.designation   = produit.designation;
            l.detail        = produit.description || '';
            l.unite         = produit.unite;
            l.prix_unitaire = parseFloat(produit.prix_unitaire) || 0;
            l.taux_tva      = parseFloat(produit.taux_tva) || tvaDefaut;
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

        ajouterDepuisCatalogue(produit) {
            this.lignes.push({
                designation:   produit.designation,
                detail:        produit.reference ? `Réf. ${produit.reference} — ${produit.fournisseur}` : '',
                unite:         produit.unite,
                quantite:      1,
                prix_unitaire: parseFloat(produit.prix) || 0,
                remise_valeur: 0,
                remise_type:   'montant',
                taux_tva:      parseFloat(produit.taux_tva) || tvaDefaut,
                est_section:   false,
                montant_ht:    parseFloat(produit.prix) || 0,
            });
            this.catalogOpen = false;
        },

        formatMontant(v) {
            return new Intl.NumberFormat('fr-BE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v || 0) + ' €';
        },
    }
}
</script>
