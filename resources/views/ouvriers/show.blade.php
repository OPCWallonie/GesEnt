<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>{{ $ouvrier->nom_complet }}</span>
            <div class="flex items-center gap-2">
                <a href="{{ route('absences.create', ['ouvrier_id' => $ouvrier->id]) }}"
                   class="text-sm border border-orange-300 text-orange-600 px-3 py-1.5 rounded-lg hover:bg-orange-50 transition">
                    + Absence
                </a>
                <a href="{{ route('ouvriers.edit', $ouvrier) }}"
                   class="text-sm bg-blue-600 text-white px-4 py-1.5 rounded-lg hover:bg-blue-700 transition">
                    Modifier
                </a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Colonne gauche : infos --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold text-lg">
                        {{ mb_strtoupper(mb_substr($ouvrier->prenom, 0, 1) . mb_substr($ouvrier->nom, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">{{ $ouvrier->nom_complet }}</div>
                        <div class="text-xs text-gray-400">CP124 – Catégorie {{ $ouvrier->categorie }} · {{ $ouvrier->anciennete }} an{{ $ouvrier->anciennete > 1 ? 's' : '' }} d'ancienneté</div>
                    </div>
                </div>

                <div class="space-y-2 text-sm">
                    @if($ouvrier->email)
                    <div class="flex items-center gap-2 text-gray-600">
                        <span class="text-gray-400 w-4">@</span>{{ $ouvrier->email }}
                    </div>
                    @endif
                    @if($ouvrier->telephone)
                    <div class="flex items-center gap-2 text-gray-600">
                        <span class="text-gray-400 w-4">☎</span>{{ $ouvrier->telephone }}
                    </div>
                    @endif
                    <div class="flex items-center gap-2 text-gray-600">
                        <span class="text-gray-400 w-4">€</span>{{ number_format($ouvrier->cout_horaire, 2, ',', ' ') }} €/h
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <span class="text-gray-400 w-4">↗</span>Entrée le {{ $ouvrier->date_entree->format('d/m/Y') }}
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    @if(! $ouvrier->actif)
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Inactif</span>
                    @elseif($ouvrier->est_disponible)
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-green-50 text-green-700 font-medium">Disponible</span>
                    @else
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-orange-50 text-orange-600 font-medium">En absence</span>
                    @endif
                </div>
            </div>

            {{-- KPIs --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="text-xs text-gray-400 mb-1">Heures cette semaine</div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($heureSem, 1) }}h</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="text-xs text-gray-400 mb-1">Coût {{ now()->year }}</div>
                    <div class="text-xl font-bold text-gray-800">{{ number_format($coutAnnee, 0, ',', ' ') }} €</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 col-span-2">
                    <div class="text-xs text-gray-400 mb-1">Repos compensatoires restants {{ now()->year }}</div>
                    <div class="text-2xl font-bold {{ $reposRestants > 0 ? 'text-green-700' : 'text-gray-400' }}">
                        {{ $reposRestants }} / 12 jours
                    </div>
                </div>
            </div>

            {{-- Absences actives --}}
            @if($absencesActives->isNotEmpty())
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                <div class="text-xs font-medium text-orange-700 uppercase mb-2">Absences en cours / à venir</div>
                @foreach($absencesActives as $absence)
                <div class="text-sm text-orange-800 flex justify-between">
                    <span>{{ $absence->libelle_type }}</span>
                    <span>{{ $absence->date_debut->format('d/m') }} → {{ $absence->date_fin->format('d/m/Y') }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Colonne droite : historique --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700 text-sm">Derniers pointages</h3>
                    <a href="{{ route('pointages.index') }}" class="text-xs text-blue-500 hover:text-blue-700">Voir le planning →</a>
                </div>
                @if($derniersPointages->isEmpty())
                    <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucun pointage enregistré.</p>
                @else
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Chantier</th>
                            <th class="px-4 py-2 text-right">Heures</th>
                            <th class="px-4 py-2 text-right">H. sup</th>
                            <th class="px-4 py-2 text-right">Coût</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($derniersPointages as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-500">{{ $p->date->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 font-medium">
                                <a href="{{ route('chantiers.show', $p->chantier) }}" class="text-gray-800 hover:text-blue-600">
                                    {{ $p->chantier->nom }}
                                </a>
                            </td>
                            <td class="px-4 py-2 text-right">{{ number_format($p->heures, 1) }}h</td>
                            <td class="px-4 py-2 text-right {{ $p->heures_sup > 0 ? 'text-orange-600' : 'text-gray-300' }}">
                                {{ $p->heures_sup > 0 ? number_format($p->heures_sup, 1).'h' : '—' }}
                            </td>
                            <td class="px-4 py-2 text-right font-medium">{{ number_format($p->cout_total, 2, ',', ' ') }} €</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
