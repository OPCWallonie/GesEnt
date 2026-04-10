<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <span>Planning / Pointages</span>
            <div class="flex items-center gap-2">
                <a href="{{ route('pointages.index', ['semaine' => $semainePrecedente]) }}"
                   class="border border-gray-300 text-gray-600 text-sm px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">← Semaine préc.</a>
                <span class="text-sm font-medium text-gray-700 px-2">
                    Semaine du {{ $lundi->format('d/m/Y') }}
                </span>
                <a href="{{ route('pointages.index', ['semaine' => $semaineSuivante]) }}"
                   class="border border-gray-300 text-gray-600 text-sm px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">Semaine suiv. →</a>
                <a href="{{ route('pointages.index') }}"
                   class="bg-blue-600 text-white text-sm px-3 py-1.5 rounded-lg hover:bg-blue-700 transition">Aujourd'hui</a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div x-data="planningGrid()" x-init="init()">

        {{-- Grille --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left w-44 sticky left-0 bg-gray-50 z-10">Ouvrier</th>
                        @foreach($jours as $jour)
                        <th class="px-2 py-3 text-center min-w-[140px]">
                            <div class="{{ $jour->isToday() ? 'text-blue-600 font-bold' : '' }}">
                                {{ ucfirst($jour->translatedFormat('l')) }}
                            </div>
                            <div class="{{ $jour->isToday() ? 'text-blue-500' : 'text-gray-400' }} font-normal normal-case">
                                {{ $jour->format('d/m') }}
                            </div>
                        </th>
                        @endforeach
                        <th class="px-3 py-3 text-right text-gray-400">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($ouvriers as $ouvrier)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-2 sticky left-0 bg-white z-10">
                            <a href="{{ route('ouvriers.show', $ouvrier) }}" class="font-medium text-gray-800 hover:text-blue-600 block">
                                {{ $ouvrier->nom_complet }}
                            </a>
                            <div class="text-xs text-gray-400">Cat. {{ $ouvrier->categorie }} · {{ number_format($ouvrier->cout_horaire, 2, ',', ' ') }} €/h</div>
                        </td>

                        @foreach($jours as $jour)
                        @php
                            $dateKey  = $jour->format('Y-m-d');
                            $pointage = $pointages[$ouvrier->id][$dateKey] ?? null;
                        @endphp
                        <td class="px-2 py-1.5 text-center align-top">
                            <div x-data="cellEditor({
                                    ouvrierDd: {{ $ouvrier->id }},
                                    date: '{{ $dateKey }}',
                                    pointageId: {{ $pointage?->id ?? 'null' }},
                                    heures: {{ $pointage?->heures ?? 0 }},
                                    heures_sup: {{ $pointage?->heures_sup ?? 0 }},
                                    chantierId: {{ $pointage?->chantier_id ?? 'null' }},
                                    chantierNom: '{{ $pointage?->chantier?->nom ?? '' }}',
                                })"
                                 class="relative">

                                {{-- Affichage compact --}}
                                <button @click="open = true"
                                        class="w-full text-center rounded-lg px-2 py-1.5 transition
                                               {{ $pointage ? 'bg-blue-50 hover:bg-blue-100 border border-blue-200' : 'hover:bg-gray-100 border border-dashed border-gray-200' }}">
                                    @if($pointage)
                                        <div class="text-sm font-semibold text-blue-700">
                                            {{ number_format($pointage->heures, 1) }}h
                                            @if($pointage->heures_sup > 0)
                                                <span class="text-orange-500">+{{ number_format($pointage->heures_sup, 1) }}h</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 truncate max-w-[120px]">{{ $pointage->chantier->nom }}</div>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </button>

                                {{-- Popover édition --}}
                                <div x-show="open" x-cloak
                                     @click.outside="open = false"
                                     class="absolute z-50 top-full left-1/2 -translate-x-1/2 mt-1 bg-white rounded-xl shadow-lg border border-gray-200 p-4 w-64 text-left">

                                    <div class="text-xs font-semibold text-gray-600 mb-3 uppercase">
                                        {{ $ouvrier->prenom }} · {{ $jour->format('d/m') }}
                                    </div>

                                    <div class="space-y-2">
                                        <div>
                                            <label class="text-xs text-gray-500 block mb-0.5">Chantier</label>
                                            <select x-model="chantierId"
                                                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                                <option value="">— Sélectionner —</option>
                                                @foreach($chantiers as $ch)
                                                    <option value="{{ $ch->id }}">{{ $ch->nom }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="text-xs text-gray-500 block mb-0.5">Heures</label>
                                                <input type="number" x-model="heures" min="0" max="24" step="0.5"
                                                       class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-500 block mb-0.5">H. sup</label>
                                                <input type="number" x-model="heures_sup" min="0" max="12" step="0.5"
                                                       class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-2 mt-3">
                                        <button @click="sauvegarder()"
                                                :disabled="loading || !chantierId || heures <= 0"
                                                class="flex-1 bg-blue-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                            <span x-show="!loading">Enregistrer</span>
                                            <span x-show="loading">...</span>
                                        </button>
                                        <button @click="supprimer()" x-show="pointageId !== null"
                                                :disabled="loading"
                                                class="bg-red-50 text-red-600 text-xs px-2 py-1.5 rounded-lg hover:bg-red-100 transition">
                                            ✕
                                        </button>
                                        <button @click="open = false"
                                                class="bg-gray-100 text-gray-500 text-xs px-2 py-1.5 rounded-lg hover:bg-gray-200 transition">
                                            Annuler
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                        @endforeach

                        {{-- Total semaine --}}
                        <td class="px-3 py-2 text-right text-sm font-medium {{ ($totaux[$ouvrier->id] ?? 0) > 40 ? 'text-orange-600' : 'text-gray-600' }}">
                            {{ number_format($totaux[$ouvrier->id] ?? 0, 1) }}h
                        </td>
                    </tr>
                    @endforeach

                    @if($ouvriers->isEmpty())
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-sm text-gray-400 text-center">
                            Aucun ouvrier actif. <a href="{{ route('ouvriers.create') }}" class="text-blue-500">Créer un ouvrier</a>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="mt-3 text-xs text-gray-400">
            Les heures en orange dépassent 40h/semaine (CP124). Heures sup majorées à 50 % pour le calcul des coûts.
        </div>
    </div>

    <script>
    function planningGrid() {
        return {
            init() {}
        };
    }

    function cellEditor(config) {
        return {
            open: false,
            loading: false,
            ouvrierDd: config.ouvrierDd,
            date: config.date,
            pointageId: config.pointageId,
            heures: config.heures,
            heures_sup: config.heures_sup,
            chantierId: config.chantierId,
            chantierNom: config.chantierNom,

            async sauvegarder() {
                if (!this.chantierId || this.heures <= 0) return;
                this.loading = true;
                try {
                    const res = await fetch('{{ route('pointages.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            ouvrier_id:  this.ouvrierDd,
                            chantier_id: this.chantierId,
                            date:        this.date,
                            heures:      this.heures,
                            heures_sup:  this.heures_sup,
                        }),
                    });
                    if (res.ok) {
                        window.location.reload();
                    }
                } finally {
                    this.loading = false;
                }
            },

            async supprimer() {
                if (!this.pointageId || !confirm('Supprimer ce pointage ?')) return;
                this.loading = true;
                try {
                    const res = await fetch(`/pointages/${this.pointageId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                    });
                    if (res.ok) {
                        window.location.reload();
                    }
                } finally {
                    this.loading = false;
                }
            },
        };
    }
    </script>
</x-app-layout>
