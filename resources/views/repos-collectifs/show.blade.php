<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <span class="font-semibold">{{ $reposCollectif->libelle }}</span>
                <span class="text-gray-400 font-normal ml-2 text-base">
                    — {{ ucfirst($reposCollectif->date->translatedFormat('l d/m/Y')) }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                @hasanyrole('admin|comptable')
                @if(! $reposCollectif->applique)
                    <form method="POST" action="{{ route('repos-collectifs.appliquer', $reposCollectif) }}"
                          onsubmit="return confirm('Générer les absences pour {{ $personnel->count() }} membre(s) du personnel ?')">
                        @csrf
                        <button type="submit"
                                class="bg-green-600 text-white text-sm font-medium px-4 py-1.5 rounded-lg hover:bg-green-700 transition">
                            ✓ Appliquer ({{ $personnel->count() }} pers.)
                        </button>
                    </form>
                    <form method="POST" action="{{ route('repos-collectifs.destroy', $reposCollectif) }}"
                          onsubmit="return confirm('Supprimer ce RC collectif ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="border border-red-200 text-red-500 text-sm px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                            Supprimer
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('repos-collectifs.annuler', $reposCollectif) }}"
                          onsubmit="return confirm('Annuler ce RC et supprimer toutes les absences générées automatiquement ?')">
                        @csrf
                        <button type="submit"
                                class="border border-orange-300 text-orange-600 text-sm px-4 py-1.5 rounded-lg hover:bg-orange-50 transition">
                            ✕ Annuler le RC
                        </button>
                    </form>
                @endif
                @endhasanyrole
                <a href="{{ route('repos-collectifs.index') }}"
                   class="border border-gray-300 text-gray-600 text-sm px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-2 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Bandeau statut --}}
    @if($reposCollectif->applique)
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl px-5 py-3 flex items-center gap-3 text-sm text-green-800">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>
            Appliqué le {{ $reposCollectif->applique_le->format('d/m/Y à H:i') }} —
            <strong>{{ $reposCollectif->absences->count() }} absence(s)</strong> générée(s).
        </span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Détails --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-4">
            <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">Détails</h3>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Date</span>
                    <span class="font-medium">{{ ucfirst($reposCollectif->date->translatedFormat('l d/m/Y')) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Durée</span>
                    <span>{{ $reposCollectif->demi_journee ? '½ journée (0,5 j)' : '1 journée complète' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Périmètre</span>
                    <span>{{ $reposCollectif->libelle_perimetre }}</span>
                </div>
                @if(! empty($reposCollectif->perimetre_valeurs))
                <div class="flex justify-between">
                    <span class="text-gray-500">Valeurs</span>
                    <span class="text-right">{{ implode(', ', $reposCollectif->perimetre_valeurs) }}</span>
                </div>
                @endif
                @if($reposCollectif->notes)
                <div class="pt-2 border-t border-gray-100">
                    <p class="text-gray-500 text-xs mb-1">Notes</p>
                    <p class="text-gray-700">{{ $reposCollectif->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Conflits (avant application) --}}
        @if(! $reposCollectif->applique)
        <div class="lg:col-span-2 space-y-4">

            @if($conflits->isNotEmpty())
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="font-semibold text-amber-800">{{ $conflits->count() }} conflit(s) détecté(s)</span>
                </div>
                <p class="text-xs text-amber-700 mb-2">Ces membres ont déjà une absence ce jour-là et seront ignorés lors de l'application :</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($conflits as $c)
                    <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">{{ $c->nom_complet }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 text-sm mb-3">
                    Personnel concerné ({{ $personnel->count() }} membre(s))
                </h3>
                @if($personnel->isEmpty())
                    <p class="text-sm text-gray-400">Aucun membre du personnel actif correspondant au périmètre.</p>
                @else
                <div class="flex flex-wrap gap-2">
                    @foreach($personnel as $p)
                    @php $enConflit = $conflits->contains('id', $p->id); @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $enConflit ? 'bg-amber-100 text-amber-700 line-through' : 'bg-blue-50 text-blue-700' }}">
                        {{ $p->nom_complet }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Absences générées (après application) --}}
        @else
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-700 text-sm">
                        Absences générées ({{ $reposCollectif->absences->count() }})
                    </h3>
                </div>
                @if($reposCollectif->absences->isEmpty())
                    <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucune absence générée.</p>
                @else
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">Membre</th>
                            <th class="px-4 py-2 text-center">Durée</th>
                            <th class="px-4 py-2 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($reposCollectif->absences->sortBy(fn($a) => $a->ouvrier->nom) as $absence)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5">
                                <a href="{{ route('ouvriers.show', $absence->ouvrier) }}"
                                   class="font-medium text-gray-800 hover:text-blue-600">
                                    {{ $absence->ouvrier->nom_complet }}
                                </a>
                            </td>
                            <td class="px-4 py-2.5 text-center text-gray-500">
                                {{ $absence->demi_journee ? '½ journée' : '1 jour' }}
                            </td>
                            <td class="px-4 py-2.5 text-right">
                                <a href="{{ route('absences.edit', $absence) }}"
                                   class="text-xs text-blue-400 hover:text-blue-600">Modifier</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
