<x-app-layout>
    <x-slot name="header">Nouveau repos compensatoire collectif</x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('repos-collectifs.store') }}"
              x-data="{
                  perimetre: 'tous',
                  valeurs: [],
                  toggleValeur(v) {
                      if (this.valeurs.includes(v)) {
                          this.valeurs = this.valeurs.filter(x => x !== v);
                      } else {
                          this.valeurs.push(v);
                      }
                  }
              }">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">

                {{-- Libellé --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Libellé <span class="text-red-500">*</span></label>
                    <input type="text" name="libelle" value="{{ old('libelle') }}" required
                           placeholder="ex : RC collectif — fête nationale"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none @error('libelle') border-red-400 @enderror">
                    @error('libelle')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Date + demi-journée --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" value="{{ old('date') }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none @error('date') border-red-400 @enderror">
                        @error('date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="demi_journee" value="1" @checked(old('demi_journee'))
                                   class="rounded border-gray-300 text-blue-600">
                            Demi-journée (0,5 j)
                        </label>
                    </div>
                </div>

                {{-- Périmètre --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Périmètre d'application</label>
                    <div class="space-y-2">
                        @foreach(\App\Models\ReposCollectif::PERIMETRES as $key => $label)
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="radio" name="perimetre" value="{{ $key }}"
                                   x-model="perimetre"
                                   @checked(old('perimetre', 'tous') === $key)
                                   class="text-blue-600">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Sélection CP --}}
                <div x-show="perimetre === 'cp'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Commissions paritaires concernées</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($commissions as $key => $label)
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" name="perimetre_valeurs[]" value="{{ $key }}"
                                   @checked(in_array($key, old('perimetre_valeurs', [])))
                                   class="rounded border-gray-300 text-blue-600">
                            {{ $key }} — {{ Str::before($label, ' —') === $key ? Str::after($label, '— ') : $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Sélection type personnel --}}
                <div x-show="perimetre === 'type'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Types de personnel concernés</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($typesPersonnel as $key => $label)
                        @if(in_array($key, \App\Models\Ouvrier::TYPES_PLANIFIABLES))
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" name="perimetre_valeurs[]" value="{{ $key }}"
                                   @checked(in_array($key, old('perimetre_valeurs', [])))
                                   class="rounded border-gray-300 text-blue-600">
                            {{ $label }}
                        </label>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                              placeholder="Informations complémentaires…"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none">{{ old('notes') }}</textarea>
                </div>

            </div>

            <div class="flex items-center gap-3 mt-4">
                <button type="submit"
                        class="bg-blue-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-blue-700 transition">
                    Créer le RC collectif
                </button>
                <a href="{{ route('repos-collectifs.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
            </div>
        </form>
    </div>
</x-app-layout>
