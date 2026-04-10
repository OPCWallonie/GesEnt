<x-app-layout>
    <x-slot name="header">
        {{ $ouvrier->exists ? 'Modifier ' . $ouvrier->nom_complet : 'Nouvel ouvrier' }}
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST"
              action="{{ $ouvrier->exists ? route('ouvriers.update', $ouvrier) : route('ouvriers.store') }}"
              class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
            @csrf
            @if($ouvrier->exists) @method('PUT') @endif

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
                <form method="POST" action="{{ route('ouvriers.destroy', $ouvrier) }}" class="ml-auto"
                      onsubmit="return confirm('Archiver cet ouvrier ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700">Archiver</button>
                </form>
                @endif
            </div>
        </form>
    </div>
</x-app-layout>
