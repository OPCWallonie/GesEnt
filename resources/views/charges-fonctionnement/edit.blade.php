<x-app-layout>
    <x-slot name="header">
        {{ $charge->exists ? 'Modifier la charge : ' . $charge->libelle : 'Nouvelle charge fixe' }}
    </x-slot>

    <div class="max-w-lg"
         x-data="{ periodicite: '{{ old('periodicite', $charge->periodicite ?? 'mensuel') }}' }">

        <form method="POST"
              action="{{ $charge->exists ? route('charges-fonctionnement.update', $charge) : route('charges-fonctionnement.store') }}">
            @csrf
            @if($charge->exists) @method('PUT') @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                    </div>
                @endif

                {{-- Libellé --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Libellé <span class="text-red-500">*</span></label>
                    <input type="text" name="libelle" value="{{ old('libelle', $charge->libelle) }}" required
                           placeholder="ex: Loyer bureau, Assurance RC…"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                {{-- Catégorie --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catégorie <span class="text-red-500">*</span></label>
                    <select name="categorie" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">— choisir —</option>
                        @foreach(\App\Models\ChargeFonctionnement::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected(old('categorie', $charge->categorie) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Périodicité + Montant --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Périodicité <span class="text-red-500">*</span></label>
                        <select name="periodicite" x-model="periodicite" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            @foreach(\App\Models\ChargeFonctionnement::PERIODICITES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            <span x-text="periodicite === 'mensuel' ? 'Montant mensuel (€)' : (periodicite === 'trimestriel' ? 'Montant trimestriel (€)' : 'Montant annuel (€)')"></span>
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="montant_mensuel"
                               value="{{ old('montant_mensuel', $charge->montant_mensuel) }}"
                               min="0" step="0.01" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <p class="text-xs text-gray-400 mt-1" x-show="periodicite !== 'mensuel'">
                            Le montant est divisé pour obtenir l'équivalent mensuel.
                        </p>
                    </div>
                </div>

                {{-- Date début / fin --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date de début <span class="text-red-500">*</span></label>
                        <input type="date" name="date_debut"
                               value="{{ old('date_debut', $charge->date_debut?->format('Y-m-d')) }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date de fin <span class="text-gray-400">(vide = en cours)</span></label>
                        <input type="date" name="date_fin"
                               value="{{ old('date_fin', $charge->date_fin?->format('Y-m-d')) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('notes', $charge->notes) }}</textarea>
                </div>

                {{-- Actif --}}
                <div class="flex items-center gap-2">
                    <input type="hidden" name="actif" value="0">
                    <input type="checkbox" name="actif" value="1" id="actif"
                           @checked(old('actif', $charge->actif ?? true))
                           class="rounded border-gray-300 text-blue-600">
                    <label for="actif" class="text-sm text-gray-700">Charge active</label>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <button type="submit"
                            class="bg-blue-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-blue-700 transition">
                        {{ $charge->exists ? 'Enregistrer' : 'Créer la charge' }}
                    </button>
                    <a href="{{ route('charges-fonctionnement.index') }}"
                       class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
