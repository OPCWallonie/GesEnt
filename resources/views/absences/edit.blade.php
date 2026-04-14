<x-app-layout>
    <x-slot name="header">
        {{ $absence->exists ? 'Modifier une absence' : 'Nouvelle absence' }}
    </x-slot>

    <div class="max-w-lg">
        <form method="POST"
              action="{{ $absence->exists ? route('absences.update', $absence) : route('absences.store') }}"
              class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            @csrf
            @if($absence->exists) @method('PUT') @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Ouvrier <span class="text-red-500">*</span></label>
                <select name="ouvrier_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">— Sélectionner —</option>
                    @foreach($ouvriers as $o)
                        <option value="{{ $o->id }}"
                                @selected(old('ouvrier_id', $absence->ouvrier_id ?? ($ouvrierId ?? null)) == $o->id)>
                            {{ $o->nom_complet }}{{ $o->actif ? '' : ' (inactif)' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type d'absence <span class="text-red-500">*</span></label>
                <select name="type" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @foreach(\App\Models\Absence::TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(old('type', $absence->type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date début <span class="text-red-500">*</span></label>
                    <input type="date" name="date_debut" value="{{ old('date_debut', $absence->date_debut?->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date fin <span class="text-red-500">*</span></label>
                    <input type="date" name="date_fin" value="{{ old('date_fin', $absence->date_fin?->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="justifie" value="0">
                <input type="checkbox" name="justifie" value="1" id="justifie"
                       @checked(old('justifie', $absence->justifie ?? true))
                       class="rounded border-gray-300 text-blue-600">
                <label for="justifie" class="text-sm text-gray-700">Absence justifiée</label>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Motif / remarque</label>
                <textarea name="motif" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('motif', $absence->motif) }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                        class="bg-blue-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-blue-700 transition">
                    {{ $absence->exists ? 'Enregistrer' : 'Créer' }}
                </button>
                <a href="{{ route('absences.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
            </div>
        </form>
    </div>
</x-app-layout>
