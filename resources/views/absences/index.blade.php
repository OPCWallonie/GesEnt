<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Absences</span>
            <a href="{{ route('absences.create') }}"
               class="inline-flex items-center gap-1 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                + Nouvelle absence
            </a>
        </div>
    </x-slot>

    {{-- Filtres --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <select name="annee" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            @for($y = now()->year; $y >= now()->year - 2; $y--)
                <option value="{{ $y }}" @selected($annee == $y)>{{ $y }}</option>
            @endfor
        </select>

        <select name="ouvrier_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">Tous les ouvriers</option>
            @foreach($ouvriers as $o)
                <option value="{{ $o->id }}" @selected(request('ouvrier_id') == $o->id)>
                    {{ $o->nom_complet }}{{ $o->actif ? '' : ' (inactif)' }}
                </option>
            @endforeach
        </select>

        <select name="type" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">Tous types</option>
            @foreach(\App\Models\Absence::TYPES as $key => $label)
                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-1.5 rounded-lg transition">
            Filtrer
        </button>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($absences->isEmpty())
            <p class="px-5 py-10 text-sm text-gray-400 text-center">Aucune absence enregistrée.</p>
        @else
        <table class="min-w-full text-sm divide-y divide-gray-100">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Ouvrier</th>
                    <th class="px-4 py-2 text-left">Type</th>
                    <th class="px-4 py-2 text-center">Du</th>
                    <th class="px-4 py-2 text-center">Au</th>
                    <th class="px-4 py-2 text-center">Jours</th>
                    <th class="px-4 py-2 text-center">Justifiée</th>
                    <th class="px-4 py-2 text-left">Motif</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($absences as $absence)
                @php
                    $enCours = $absence->date_debut->lte(today()) && $absence->date_fin->gte(today());
                    $aVenir  = $absence->date_debut->gt(today());
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">
                        <a href="{{ route('ouvriers.show', $absence->ouvrier) }}" class="text-gray-800 hover:text-blue-600">
                            {{ $absence->ouvrier->nom_complet }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $absence->type === 'maladie' ? 'bg-red-50 text-red-600' :
                               ($absence->type === 'accident_travail' ? 'bg-red-100 text-red-700' :
                               ($absence->type === 'conge' ? 'bg-green-50 text-green-700' :
                               ($absence->type === 'repos_compensatoire' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600'))) }}">
                            {{ $absence->libelle_type }}
                        </span>
                        @if($absence->repos_collectif_id)
                            <a href="{{ route('repos-collectifs.show', $absence->repos_collectif_id) }}"
                               class="ml-1 text-xs text-indigo-500 hover:text-indigo-700 font-medium">
                                (collectif)
                            </a>
                        @endif
                        @if($enCours)
                            <span class="ml-1 text-xs text-orange-500 font-medium">En cours</span>
                        @elseif($aVenir)
                            <span class="ml-1 text-xs text-blue-400">À venir</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $absence->date_debut->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $absence->date_fin->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center font-medium">{{ $absence->nb_jours }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($absence->justifie)
                            <span class="text-green-600 text-xs">Oui</span>
                        @else
                            <span class="text-red-500 text-xs">Non</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $absence->motif ?? '—' }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('absences.edit', $absence) }}" class="text-xs text-blue-500 hover:text-blue-700">Modifier</a>
                        <form method="POST" action="{{ route('absences.destroy', $absence) }}" class="inline"
                              onsubmit="return confirm('Supprimer cette absence ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    @if($absences->hasPages())
        <div class="mt-4">{{ $absences->links() }}</div>
    @endif
</x-app-layout>
