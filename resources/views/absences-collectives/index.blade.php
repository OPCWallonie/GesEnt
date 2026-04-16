<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <span>Absences collectives</span>
            <div class="flex items-center gap-3">
                <select onchange="window.location.href='?annee='+this.value+'&type={{ request('type') }}'"
                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                    @for($y = now()->year + 1; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" @selected($y === $annee)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div x-data="{ tab: '{{ request('type', 'repos_compensatoire') }}' }">

        {{-- Onglets --}}
        <div class="flex gap-0 border-b border-gray-200 mb-5">
            <button @click="tab = 'repos_compensatoire'"
                    :class="tab === 'repos_compensatoire'
                        ? 'border-blue-500 text-blue-700 bg-white'
                        : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-5 py-2.5 text-sm font-medium border-b-2 transition -mb-px">
                Repos compensatoires
                @if(isset($absencesParType['repos_compensatoire']))
                    <span class="ml-1.5 bg-blue-100 text-blue-700 text-xs px-1.5 py-0.5 rounded-full">
                        {{ $absencesParType['repos_compensatoire']->count() }}
                    </span>
                @endif
            </button>
            <button @click="tab = 'report_ferie'"
                    :class="tab === 'report_ferie'
                        ? 'border-purple-500 text-purple-700 bg-white'
                        : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-5 py-2.5 text-sm font-medium border-b-2 transition -mb-px">
                Reports jours fériés
                @if(isset($absencesParType['report_ferie']))
                    <span class="ml-1.5 bg-purple-100 text-purple-700 text-xs px-1.5 py-0.5 rounded-full">
                        {{ $absencesParType['report_ferie']->count() }}
                    </span>
                @endif
            </button>
            <button @click="tab = 'conge_entreprise'"
                    :class="tab === 'conge_entreprise'
                        ? 'border-green-500 text-green-700 bg-white'
                        : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-5 py-2.5 text-sm font-medium border-b-2 transition -mb-px">
                Congés d'entreprise
                @if(isset($absencesParType['conge_entreprise']))
                    <span class="ml-1.5 bg-green-100 text-green-700 text-xs px-1.5 py-0.5 rounded-full">
                        {{ $absencesParType['conge_entreprise']->count() }}
                    </span>
                @endif
            </button>
        </div>

        {{-- Bouton création --}}
        @hasanyrole('admin|comptable')
        <div class="mb-4">
            <a :href="`{{ url('absences-collectives/create') }}?type=${tab}`"
               class="inline-flex items-center gap-1.5 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                + Nouvelle absence collective
            </a>
        </div>
        @endhasanyrole

        {{-- Tableaux par type --}}
        @foreach(['repos_compensatoire' => ['label' => 'Repos compensatoires', 'color' => 'blue'], 'report_ferie' => ['label' => 'Reports jours fériés', 'color' => 'purple'], 'conge_entreprise' => ['label' => 'Congés d\'entreprise', 'color' => 'green']] as $typeKey => $typeInfo)
        <div x-show="tab === '{{ $typeKey }}'" x-cloak>
            @php $liste = $absencesParType[$typeKey] ?? collect(); @endphp

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                @if($liste->isEmpty())
                    <p class="px-5 py-10 text-sm text-gray-400 text-center">
                        Aucune entrée pour ce type en {{ $annee }}.
                        @hasanyrole('admin|comptable')
                        <a :href="`{{ url('absences-collectives/create') }}?type={{ $typeKey }}`" class="text-blue-500">En créer une</a>.
                        @endhasanyrole
                    </p>
                @else
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Libellé</th>
                            <th class="px-4 py-2 text-center">Durée</th>
                            <th class="px-4 py-2 text-center">Périmètre</th>
                            <th class="px-4 py-2 text-center">Statut</th>
                            <th class="px-4 py-2 text-right">Absences</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($liste as $ac)
                        @php $passe = $ac->date->isPast(); @endphp
                        <tr class="hover:bg-gray-50 {{ $passe && ! $ac->applique ? 'opacity-60' : '' }}">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ ucfirst($ac->date->translatedFormat('l d/m/Y')) }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                <a href="{{ route('absences-collectives.show', $ac) }}" class="hover:text-blue-600">
                                    {{ $ac->libelle }}
                                </a>
                                @if($ac->notes)
                                    <div class="text-xs text-gray-400 truncate max-w-xs">{{ $ac->notes }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">
                                {{ $ac->demi_journee ? '½ journée' : '1 jour' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($ac->perimetre === 'tous')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700">Tous</span>
                                @elseif($ac->perimetre === 'cp')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-violet-50 text-violet-700">
                                        {{ implode(', ', $ac->perimetre_valeurs ?? []) }}
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">
                                        {{ implode(', ', $ac->perimetre_valeurs ?? []) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($ac->applique)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-green-50 text-green-700">Appliqué</span>
                                @elseif($ac->date->isFuture() || $ac->date->isToday())
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-amber-50 text-amber-600">En attente</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Non appliqué</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500">
                                {{ $ac->applique ? $ac->absences()->count() : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('absences-collectives.show', $ac) }}" class="text-xs text-blue-500 hover:text-blue-700">
                                    Détail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            {{-- Solde RC par personne (uniquement pour l'onglet repos_compensatoire) --}}
            @if($typeKey === 'repos_compensatoire' && $soldeRcParPersonne->isNotEmpty())
            <div class="mt-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Solde RC par personne — {{ $annee }}</h3>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="min-w-full text-sm divide-y divide-gray-100">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-2 text-left">Membre</th>
                                <th class="px-4 py-2 text-center">Quota</th>
                                <th class="px-4 py-2 text-center">Utilisés</th>
                                <th class="px-4 py-2 text-center">Restants</th>
                                <th class="px-4 py-2 text-right w-32">Progression</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($soldeRcParPersonne->sortBy(fn($r) => $r['ouvrier']->nom) as $solde)
                            @php
                                $pct = $solde['quota'] > 0 ? min(100, round(($solde['utilises'] / $solde['quota']) * 100)) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5">
                                    <a href="{{ route('ouvriers.show', $solde['ouvrier']) }}"
                                       class="font-medium text-gray-800 hover:text-blue-600">
                                        {{ $solde['ouvrier']->nom_complet }}
                                    </a>
                                </td>
                                <td class="px-4 py-2.5 text-center text-gray-500">{{ $solde['quota'] }} j</td>
                                <td class="px-4 py-2.5 text-center text-gray-600">{{ number_format($solde['utilises'], 1) }} j</td>
                                <td class="px-4 py-2.5 text-center font-medium {{ $solde['restants'] > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ number_format($solde['restants'], 1) }} j
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ $pct >= 100 ? 'bg-red-400' : ($pct >= 75 ? 'bg-orange-400' : 'bg-green-400') }}"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        @endforeach

    </div>
</x-app-layout>
