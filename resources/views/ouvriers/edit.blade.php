<x-app-layout>
    <x-slot name="header">
        {{ $ouvrier->exists ? 'Modifier ' . $ouvrier->nom_complet : 'Nouvel ouvrier' }}
    </x-slot>

    <div class="max-w-2xl space-y-6">
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

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">N° national</label>
                    <input type="text" name="numero_national" value="{{ old('numero_national', $ouvrier->numero_national) }}"
                           placeholder="ex: 80.01.01-123.45"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catégorie CP124 <span class="text-red-500">*</span></label>
                    <select name="categorie" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach(\App\Models\Ouvrier::CATEGORIES as $cat)
                            <option value="{{ $cat }}" @selected(old('categorie', $ouvrier->categorie) === $cat)>Catégorie {{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Coût horaire (€) <span class="text-red-500">*</span></label>
                    <input type="number" name="cout_horaire" value="{{ old('cout_horaire', $ouvrier->cout_horaire) }}"
                           min="0" step="0.01" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-xs text-gray-400 mt-1">Heures sup majorées +50 % automatiquement.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date d'entrée <span class="text-red-500">*</span></label>
                    <input type="date" name="date_entree" value="{{ old('date_entree', $ouvrier->date_entree?->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

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

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                <textarea name="notes" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('notes', $ouvrier->notes) }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="actif" value="0">
                <input type="checkbox" name="actif" value="1" id="actif"
                       @checked(old('actif', $ouvrier->actif ?? true))
                       class="rounded border-gray-300 text-blue-600">
                <label for="actif" class="text-sm text-gray-700">Ouvrier actif</label>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                        class="bg-blue-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-blue-700 transition">
                    {{ $ouvrier->exists ? 'Enregistrer' : 'Créer l\'ouvrier' }}
                </button>
                <a href="{{ $ouvrier->exists ? route('ouvriers.show', $ouvrier) : route('ouvriers.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>

                @if($ouvrier->exists)
                <button type="submit" form="form-archiver"
                        class="ml-auto text-sm text-red-500 hover:text-red-700"
                        onclick="return confirm('Archiver cet ouvrier ?')">Archiver</button>
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
                        {{-- Type --}}
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
                        {{-- Date obtention --}}
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-500 mb-0.5">Obtention *</label>
                            <input type="date" :name="`certifications[${index}][date_obtention]`" x-model="ligne.date_obtention"
                                   class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-blue-400">
                        </div>
                        {{-- Date expiration --}}
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-500 mb-0.5">Expiration</label>
                            <input type="date" :name="`certifications[${index}][date_expiration]`" x-model="ligne.date_expiration"
                                   class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-blue-400"
                                   placeholder="Auto">
                        </div>
                        {{-- Organisme --}}
                        <div class="col-span-3">
                            <label class="block text-xs text-gray-500 mb-0.5">Organisme</label>
                            <input type="text" :name="`certifications[${index}][organisme]`" x-model="ligne.organisme"
                                   placeholder="ex: Constructiv"
                                   class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-blue-400">
                        </div>
                        {{-- Supprimer --}}
                        <div class="col-span-1 flex items-end pb-0.5">
                            <button type="button" @click="supprimer(index)"
                                    class="text-gray-300 hover:text-red-400 transition mt-5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        {{-- ID caché --}}
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
