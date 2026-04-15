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
                @php $semainePrecLibelle = $lundi->copy()->subWeek()->format('d/m'); @endphp
                <form method="POST" action="{{ route('pointages.copier') }}"
                      onsubmit="return confirm('Recopier le planning de la semaine du {{ $semainePrecLibelle }} ?\n(Les créneaux déjà saisis ne seront pas écrasés.)')">
                    @csrf
                    <input type="hidden" name="semaine" value="{{ $lundi->format('Y-m-d') }}">
                    <button type="submit"
                            class="border {{ $pointages->isEmpty() ? 'border-indigo-300 text-indigo-600 hover:bg-indigo-50' : 'border-gray-300 text-gray-500 hover:bg-gray-50' }} text-sm px-3 py-1.5 rounded-lg transition">
                        ↩ Recopier sem. préc.
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Alerte RC collectifs non appliqués cette semaine --}}
    @if($reposCollectifsEnAttente->isNotEmpty())
    <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
                <div class="font-semibold text-amber-800 text-sm mb-1">
                    {{ $reposCollectifsEnAttente->count() }} RC collectif(s) non appliqué(s) cette semaine
                </div>
                <div class="space-y-1">
                    @foreach($reposCollectifsEnAttente as $rc)
                    <div class="flex items-center justify-between text-sm text-amber-700">
                        <span>
                            <span class="font-medium">{{ $rc->date->format('d/m') }}</span>
                            — {{ $rc->libelle }}
                            {{ $rc->demi_journee ? '(½ j)' : '' }}
                        </span>
                        @hasanyrole('admin|comptable')
                        <a href="{{ route('repos-collectifs.show', $rc) }}"
                           class="text-xs underline hover:text-amber-900 ml-3">
                            Appliquer →
                        </a>
                        @endhasanyrole
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
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
                            $dateKey     = $jour->format('Y-m-d');
                            $pointage    = $pointages[$ouvrier->id][$dateKey] ?? null;

                            // Absence couvrant ce jour précis
                            $absenceJour = null;
                            foreach (($absences[$ouvrier->id] ?? []) as $abs) {
                                if ($abs->date_debut->lte($jour) && $abs->date_fin->gte($jour)) {
                                    $absenceJour = $abs;
                                    break;
                                }
                            }

                            // Style du badge d'absence
                            $absBg = match($absenceJour?->type) {
                                'maladie', 'accident_travail' => ['bg' => 'bg-red-50',  'border' => 'border-red-200',  'text' => 'text-red-600'],
                                'conge'                       => ['bg' => 'bg-sky-50',  'border' => 'border-sky-200',  'text' => 'text-sky-600'],
                                'repos_compensatoire'         => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-600'],
                                default                       => ['bg' => 'bg-orange-50','border'=> 'border-orange-200','text'=> 'text-orange-600'],
                            };
                        @endphp
                        <td class="px-2 py-1.5 text-center align-top">

                            {{-- Cas 1 : absent ce jour ET pas de pointage → badge non-interactif --}}
                            @if($absenceJour && ! $pointage)
                                <div class="w-full rounded-lg px-2 py-1.5 border {{ $absBg['bg'] }} {{ $absBg['border'] }}">
                                    <div class="text-xs font-medium {{ $absBg['text'] }}">
                                        {{ $absenceJour->libelle_type }}
                                    </div>
                                </div>

                            {{-- Cas 2 : normal (avec ou sans pointage, absence ignorée si pointage saisi) --}}
                            @else
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
                                               {{ $pointage
                                                    ? ($absenceJour ? 'bg-orange-50 hover:bg-orange-100 border border-orange-300' : 'bg-blue-50 hover:bg-blue-100 border border-blue-200')
                                                    : 'hover:bg-gray-100 border border-dashed border-gray-200' }}">
                                    @if($pointage)
                                        @if($absenceJour)
                                            <div class="text-xs text-orange-500 leading-none mb-0.5">⚠ {{ $absenceJour->libelle_type }}</div>
                                        @endif
                                        <div class="text-sm font-semibold {{ $absenceJour ? 'text-orange-700' : 'text-blue-700' }}">
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

                                    @if($absenceJour)
                                    <div class="mb-3 px-2 py-1.5 rounded-lg text-xs {{ $absBg['bg'] }} {{ $absBg['text'] }} {{ $absBg['border'] }} border">
                                        ⚠ {{ $absenceJour->libelle_type }} — encodage possible mais vérifiez.
                                    </div>
                                    @endif

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
                            @endif

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
            Les heures en orange dépassent 40h/semaine. Les heures sup sont majorées selon la CP de chaque membre du personnel.
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
