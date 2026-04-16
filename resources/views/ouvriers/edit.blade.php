<x-app-layout>
    <x-slot name="header">
        {{ $ouvrier->exists ? 'Modifier ' . $ouvrier->nom_complet : 'Nouveau membre du personnel' }}
    </x-slot>

    @php
        $categoriesParCp = \App\Models\Ouvrier::CATEGORIES_PAR_CP;
        $currentCp       = old('commission_paritaire', $ouvrier->commission_paritaire ?? 'CP124');
        $currentCat      = old('categorie', $ouvrier->categorie ?? '');
        $currentType     = old('type_personnel', $ouvrier->type_personnel ?? 'ouvrier');
        $isActif         = old('actif', $ouvrier->actif ?? true);
    @endphp

    <div class="max-w-2xl space-y-6"
         x-data="personnelForm(
             {{ json_encode((bool) $isActif) }},
             {{ json_encode($currentCp) }},
             {{ json_encode($currentCat) }},
             {{ json_encode($currentType) }},
             {{ json_encode($categoriesParCp) }}
         )">

        <form method="POST"
              action="{{ $ouvrier->exists ? route('ouvriers.update', $ouvrier) : route('ouvriers.store') }}"
              class="space-y-6">
            @csrf
            @if($ouvrier->exists) @method('PUT') @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                </div>
            @endif

            {{-- Type de personnel --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type de personnel <span class="text-red-500">*</span></label>
                <select name="type_personnel" x-model="typePersonnel" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @foreach(\App\Models\Ouvrier::TYPES_PERSONNEL as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Nom / Prénom --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Prénom <span class="text-red-500">*</span></label>
                    <input type="text" name="prenom" value="{{ old('prenom', $ouvrier->prenom) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nom <span class="text-red-500">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom', $ouvrier->nom) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            {{-- N° national --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">N° national</label>
                <input type="text" name="numero_national" value="{{ old('numero_national', $ouvrier->numero_national) }}"
                       placeholder="ex: 80.01.01-123.45"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            {{-- Commission paritaire + Catégorie --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Commission paritaire <span class="text-red-500">*</span></label>
                    <select name="commission_paritaire" x-model="cp" @change="onCpChange()" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach(\App\Models\Ouvrier::COMMISSIONS_PARITAIRES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="categoriesDisponibles.length > 0" x-cloak>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catégorie</label>
                    <select name="categorie" x-model="categorie"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">— aucune —</option>
                        <template x-for="cat in categoriesDisponibles" :key="cat">
                            <option :value="cat" :selected="categorie === cat" x-text="'Catégorie ' + cat"></option>
                        </template>
                    </select>
                </div>
                {{-- Champ vide pour la catégorie quand la CP n'en a pas --}}
                <input type="hidden" name="categorie" x-show="categoriesDisponibles.length === 0" value="">
            </div>

            {{-- Coût --}}
            <div class="grid grid-cols-2 gap-4">
                {{-- Coût horaire : ouvriers et employés terrain uniquement --}}
                <div x-show="typePersonnel === 'ouvrier' || typePersonnel === 'employe_terrain'" x-cloak>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Coût horaire chargé (€/h) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="cout_horaire" value="{{ old('cout_horaire', $ouvrier->cout_horaire) }}"
                           min="0" step="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-xs text-gray-400 mt-1">
                        Coût total employeur : salaire brut + ONSS patronal + assurances + avantages.<br>
                        Heures sup majorées +50 % automatiquement.
                    </p>
                </div>

                {{-- Coût mensuel : admin / direction --}}
                <div x-show="typePersonnel === 'employe_admin' || typePersonnel === 'direction'" x-cloak>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Coût mensuel chargé (€/mois)
                    </label>
                    <input type="number" name="cout_mensuel" value="{{ old('cout_mensuel', $ouvrier->cout_mensuel) }}"
                           min="0" step="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-xs text-gray-400 mt-1">
                        Coût total employeur : brut + charges patronales + avantages. Pas le salaire brut seul.<br>
                        Équivalent horaire (÷ 164,54 h) : <span x-text="coutHoraireEquivalent()"></span> €/h
                    </p>
                </div>
            </div>

            {{-- Heures par semaine --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Heures / semaine <span class="text-red-500">*</span></label>
                    <input type="number" name="heures_semaine" x-ref="heuresSemaine"
                           value="{{ old('heures_semaine', $ouvrier->heures_semaine ?? 40) }}"
                           @input="quotaRcPreview = calculerQuotaRC()"
                           step="0.5" min="20" max="50" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none @error('heures_semaine') border-red-400 @enderror">
                    <p class="text-xs mt-1" :class="quotaRcPreview.quota > 0 ? 'text-gray-400' : 'text-gray-300'">
                        Quota RC : <span x-text="quotaRcPreview.quota + ' j/an'"></span>
                        <span x-show="quotaRcPreview.plafonne" class="text-amber-500">(plafonné)</span>
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date d'entrée <span class="text-red-500">*</span></label>
                    <input type="date" name="date_entree" value="{{ old('date_entree', $ouvrier->date_entree?->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            {{-- Jours de congés supplémentaires --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Jours de congés supplémentaires</label>
                <input type="number" name="jours_conges_supplementaires" min="0" max="20"
                       value="{{ old('jours_conges_supplementaires', $ouvrier->jours_conges_supplementaires ?? 0) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <p class="text-xs text-gray-400 mt-1">Jours offerts en plus des 20 légaux (ancienneté, cadeau d'entreprise…).</p>
            </div>

            {{-- Mode heures supplémentaires par défaut --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">Heures sup. par défaut</label>
                @php $modeDefaut = old('mode_heures_sup_defaut', $ouvrier->mode_heures_sup_defaut ?? 'payees'); @endphp
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="mode_heures_sup_defaut" value="payees"
                               {{ $modeDefaut === 'payees' ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Payées <span class="text-gray-400">(+50 % majoration)</span></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="mode_heures_sup_defaut" value="recuperees"
                               {{ $modeDefaut === 'recuperees' ? 'checked' : '' }}
                               class="text-orange-500 focus:ring-orange-400">
                        <span class="text-sm text-orange-600 font-medium">Récupérées <span class="text-gray-400">(contrepartie = jour de repos)</span></span>
                    </label>
                </div>
            </div>

            {{-- Téléphone / Email --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone', $ouvrier->telephone) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $ouvrier->email) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                <textarea name="notes" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('notes', $ouvrier->notes) }}</textarea>
            </div>

            {{-- Actif / Désactivation --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="actif" value="0">
                    <input type="checkbox" name="actif" value="1" id="actif"
                           x-model="actif"
                           class="rounded border-gray-300 text-blue-600">
                    <label for="actif" class="text-sm text-gray-700">Membre actif</label>
                </div>

                {{-- Bloc désactivation — visible quand actif est décoché --}}
                <div x-show="!actif" x-cloak
                     class="border border-orange-200 bg-orange-50 rounded-lg p-4 space-y-3">
                    <p class="text-sm font-medium text-orange-800">Informations de départ</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-orange-700 mb-1">Date de départ <span class="text-red-500">*</span></label>
                            <input type="date" name="date_sortie"
                                   value="{{ old('date_sortie', $ouvrier->date_sortie?->format('Y-m-d')) }}"
                                   :required="!actif"
                                   class="w-full border border-orange-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-orange-700 mb-1">Motif de départ <span class="text-red-500">*</span></label>
                            <select name="motif_sortie" :required="!actif"
                                    class="w-full border border-orange-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                                <option value="">— choisir —</option>
                                @foreach(\App\Models\Ouvrier::MOTIFS_SORTIE as $key => $label)
                                    <option value="{{ $key }}" @selected(old('motif_sortie', $ouvrier->motif_sortie) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                        class="bg-blue-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-blue-700 transition">
                    {{ $ouvrier->exists ? 'Enregistrer' : 'Créer le membre' }}
                </button>
                <a href="{{ $ouvrier->exists ? route('ouvriers.show', $ouvrier) : route('ouvriers.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>

                @if($ouvrier->exists)
                <button type="submit" form="form-archiver"
                        class="ml-auto text-sm text-red-500 hover:text-red-700"
                        onclick="return confirm('Archiver ce membre du personnel ?')">Archiver</button>
                @endif
            </div>
            </div>{{-- /bg-white --}}

            {{-- ──── Certifications ──── --}}
            @php
                $certsInitiales = old('certifications', $ouvrier->certifications->map(fn($c) => [
                    'id'               => $c->id,
                    'type'             => $c->type,
                    'date_obtention'   => $c->date_obtention->format('Y-m-d'),
                    'date_expiration'  => $c->date_expiration?->format('Y-m-d') ?? '',
                    'organisme'        => $c->organisme ?? '',
                    'numero_certificat'=> $c->numero_certificat ?? '',
                    'notes'            => $c->notes ?? '',
                ])->values()->toArray());
            @endphp

            <div x-data="certEditor({{ json_encode($certsInitiales) }}, {{ json_encode(collect($certificationTypes)->map(fn($v, $k) => ['key' => $k, 'label' => $v['label']])->values()) }})"
                 class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">

                <div class="flex items-center justify-between border-b pb-2">
                    <h2 class="font-semibold text-gray-700">Certifications & habilitations</h2>
                    <button type="button" @click="ajouter()"
                            class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Ajouter
                    </button>
                </div>

                <template x-if="lignes.length === 0">
                    <p class="text-sm text-gray-400 py-2">Aucune certification enregistrée.</p>
                </template>

                <template x-for="(ligne, index) in lignes" :key="index">
                    <div class="grid grid-cols-12 gap-2 items-start py-2 border-b border-gray-50 last:border-0">
                        <div class="col-span-4">
                            <label class="block text-xs text-gray-500 mb-0.5">Type *</label>
                            <select :name="`certifications[${index}][type]`" x-model="ligne.type"
                                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-blue-400">
                                <option value="">— choisir —</option>
                                <template x-for="t in types" :key="t.key">
                                    <option :value="t.key" :selected="ligne.type === t.key" x-text="t.label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-500 mb-0.5">Obtention *</label>
                            <input type="date" :name="`certifications[${index}][date_obtention]`" x-model="ligne.date_obtention"
                                   class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-blue-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-500 mb-0.5">Expiration</label>
                            <input type="date" :name="`certifications[${index}][date_expiration]`" x-model="ligne.date_expiration"
                                   class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-blue-400"
                                   placeholder="Auto">
                        </div>
                        <div class="col-span-3">
                            <label class="block text-xs text-gray-500 mb-0.5">Organisme</label>
                            <input type="text" :name="`certifications[${index}][organisme]`" x-model="ligne.organisme"
                                   placeholder="ex: Constructiv"
                                   class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-blue-400">
                        </div>
                        <div class="col-span-1 flex items-end pb-0.5">
                            <button type="button" @click="supprimer(index)"
                                    class="text-gray-300 hover:text-red-400 transition mt-5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <input type="hidden" :name="`certifications[${index}][id]`" :value="ligne.id ?? ''">
                        <input type="hidden" :name="`certifications[${index}][notes]`" :value="ligne.notes ?? ''">
                        <input type="hidden" :name="`certifications[${index}][numero_certificat]`" :value="ligne.numero_certificat ?? ''">
                    </div>
                </template>
            </div>

        </form>
    </div>

    @if($ouvrier->exists)
    <form id="form-archiver" method="POST" action="{{ route('ouvriers.destroy', $ouvrier) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
    @endif

<script>
function personnelForm(actifInit, cpInit, catInit, typeInit, categoriesParCp) {
    return {
        actif: actifInit,
        cp: cpInit,
        categorie: catInit,
        typePersonnel: typeInit,
        categoriesParCp: categoriesParCp,
        quotaRcPreview: { quota: 0, plafonne: false },

        init() {
            this.quotaRcPreview = this.calculerQuotaRC();
        },

        get categoriesDisponibles() {
            return this.categoriesParCp[this.cp] ?? [];
        },

        onCpChange() {
            // Si la catégorie actuelle n'existe plus dans la nouvelle CP, réinitialiser
            if (! this.categoriesDisponibles.includes(this.categorie)) {
                this.categorie = '';
            }
            this.quotaRcPreview = this.calculerQuotaRC();
        },

        calculerQuotaRC() {
            const h = parseFloat(this.$refs.heuresSemaine?.value ?? 40);
            if (h <= 38) return { quota: 0, plafonne: false };
            const hJour = h / 5;
            const joursCalc = Math.floor((h - 38) * 52 / hJour);
            const plafonds = { CP124: 12 };
            const plafond = plafonds[this.cp] ?? null;
            if (plafond !== null && joursCalc > plafond) {
                return { quota: plafond, plafonne: true };
            }
            return { quota: joursCalc, plafonne: false };
        },

        coutHoraireEquivalent() {
            const el = document.querySelector('input[name="cout_mensuel"]');
            const val = parseFloat(el ? el.value : 0);
            if (val > 0) return (val / 164.54).toFixed(2);
            return '—';
        },
    };
}

function certEditor(initial, types) {
    return {
        lignes: initial.map(l => ({...l})),
        types: types,

        ajouter() {
            this.lignes.push({
                id: '', type: '', date_obtention: '', date_expiration: '',
                organisme: '', numero_certificat: '', notes: '',
            });
        },

        supprimer(index) {
            this.lignes.splice(index, 1);
        },
    };
}
</script>
</x-app-layout>
