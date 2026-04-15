<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>{{ $ouvrier->nom_complet }}</span>
            <div class="flex items-center gap-2">
                @if($ouvrier->est_planifiable)
                <a href="{{ route('absences.create', ['ouvrier_id' => $ouvrier->id]) }}"
                   class="text-sm border border-orange-300 text-orange-600 px-3 py-1.5 rounded-lg hover:bg-orange-50 transition">
                    + Absence
                </a>
                @endif
                <a href="{{ route('ouvriers.edit', $ouvrier) }}"
                   class="text-sm bg-blue-600 text-white px-4 py-1.5 rounded-lg hover:bg-blue-700 transition">
                    Modifier
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Bandeau inactif --}}
    @if(! $ouvrier->actif)
    <div class="bg-gray-100 border border-gray-300 rounded-xl px-5 py-3 mb-4 flex items-center gap-3 text-sm text-gray-700">
        <svg class="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span>
            <span class="font-semibold">Inactif</span>
            @if($ouvrier->date_sortie)
                depuis le {{ $ouvrier->date_sortie->format('d/m/Y') }}
            @endif
            @if($ouvrier->motif_sortie)
                — Motif : {{ \App\Models\Ouvrier::MOTIFS_SORTIE[$ouvrier->motif_sortie] ?? $ouvrier->motif_sortie }}
            @endif
        </span>
    </div>
    @endif

    {{-- Alerte certifications --}}
    @if($certificationsAlerte->isNotEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="font-semibold text-amber-800">{{ $certificationsAlerte->count() }} certification(s) à renouveler</span>
        </div>
        <div class="space-y-0.5">
            @foreach($certificationsAlerte as $c)
            <div class="text-sm text-amber-700 flex items-center justify-between">
                <span>{{ $c->libelle_type }}</span>
                @if($c->est_expiree)
                    <span class="text-red-600 font-medium">Expirée le {{ $c->date_expiration->format('d/m/Y') }}</span>
                @else
                    <span>Expire le {{ $c->date_expiration->format('d/m/Y') }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Colonne gauche : infos --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
                @php
                    $typeBadge = match($ouvrier->type_personnel) {
                        'ouvrier'         => 'bg-blue-50 text-blue-700',
                        'employe_terrain' => 'bg-violet-50 text-violet-700',
                        'employe_admin'   => 'bg-gray-100 text-gray-600',
                        'direction'       => 'bg-amber-50 text-amber-700',
                        default           => 'bg-gray-100 text-gray-500',
                    };
                    $typeLabel = \App\Models\Ouvrier::TYPES_PERSONNEL[$ouvrier->type_personnel] ?? $ouvrier->type_personnel;
                @endphp
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold text-lg">
                        {{ mb_strtoupper(mb_substr($ouvrier->prenom, 0, 1) . mb_substr($ouvrier->nom, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">{{ $ouvrier->nom_complet }}</div>
                        <div class="flex items-center gap-1 mt-0.5">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBadge }}">{{ $typeLabel }}</span>
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $ouvrier->label_cp }} · {{ $ouvrier->anciennete }} an{{ $ouvrier->anciennete > 1 ? 's' : '' }} d'ancienneté</div>
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

                    @if($ouvrier->cout_horaire > 0)
                    <div class="flex items-center gap-2 text-gray-600">
                        <span class="text-gray-400 w-4">€</span>{{ number_format($ouvrier->cout_horaire, 2, ',', ' ') }} €/h
                    </div>
                    @elseif($ouvrier->cout_mensuel > 0)
                    <div class="flex items-center gap-2 text-gray-600">
                        <span class="text-gray-400 w-4">€</span>{{ number_format($ouvrier->cout_mensuel, 0, ',', ' ') }} €/mois
                        <span class="text-xs text-gray-400">(≈ {{ number_format($ouvrier->cout_horaire_effectif, 2, ',', ' ') }} €/h)</span>
                    </div>
                    @endif

                    <div class="flex items-center gap-2 text-gray-600">
                        <span class="text-gray-400 w-4">↗</span>Entrée le {{ $ouvrier->date_entree->format('d/m/Y') }}
                    </div>
                    @if($ouvrier->heures_semaine)
                    <div class="flex items-center gap-2 text-gray-600">
                        <span class="text-gray-400 w-4">⏱</span>
                        {{ number_format($ouvrier->heures_semaine, 1) }}h/semaine
                        @if($ouvrier->quota_rc_annuel > 0)
                            <span class="text-xs text-blue-500">→ {{ $ouvrier->quota_rc_annuel }} RC/an</span>
                        @endif
                    </div>
                    @endif
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
            @if($ouvrier->est_planifiable)
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="text-xs text-gray-400 mb-1">Heures cette semaine</div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($heureSem, 1) }}h</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="text-xs text-gray-400 mb-1">Coût {{ now()->year }}</div>
                    <div class="text-xl font-bold text-gray-800">{{ number_format($coutAnnee, 0, ',', ' ') }} €</div>
                </div>
                @php
                    $quota    = $ouvrier->quota_rc_annuel;
                    $utilises = $ouvrier->reposCompensatoiresUtilises(now()->year);
                    $rcPct    = $quota > 0 ? min(100, round(($utilises / $quota) * 100)) : 0;
                @endphp
                @if($quota > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-4 col-span-2">
                    <div class="flex items-center justify-between mb-1">
                        <div class="text-xs text-gray-400">Repos compensatoires {{ now()->year }}</div>
                        <a href="{{ route('repos-collectifs.index') }}" class="text-xs text-blue-400 hover:text-blue-600">
                            Voir calendrier
                        </a>
                    </div>
                    <div class="flex items-end gap-2 mb-2">
                        <span class="text-2xl font-bold {{ $reposRestants > 0 ? 'text-green-700' : 'text-gray-400' }}">
                            {{ number_format($reposRestants, 1) }}
                        </span>
                        <span class="text-sm text-gray-400 mb-0.5">/ {{ $quota }} j restants</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $rcPct >= 100 ? 'bg-red-400' : ($rcPct >= 75 ? 'bg-orange-400' : 'bg-green-400') }}"
                             style="width: {{ $rcPct }}%"></div>
                    </div>
                    <div class="text-xs text-gray-400 mt-1">{{ number_format($utilises, 1) }} jour(s) utilisé(s)</div>
                </div>
                @endif
            </div>
            @else
            {{-- Employés admin / direction : coût mensuel en frais généraux --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="text-xs text-gray-400 mb-1">Coût mensuel</div>
                @if($ouvrier->cout_mensuel > 0)
                    <div class="text-xl font-bold text-gray-800">{{ number_format($ouvrier->cout_mensuel, 0, ',', ' ') }} €/mois</div>
                @elseif($ouvrier->cout_horaire > 0)
                    <div class="text-xl font-bold text-gray-800">{{ number_format($ouvrier->cout_horaire, 2, ',', ' ') }} €/h</div>
                @else
                    <div class="text-gray-400 text-sm">Non renseigné</div>
                @endif
                <p class="text-xs text-gray-400 mt-1">Coût réparti en frais généraux</p>
            </div>
            @endif

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

        {{-- Colonne droite : certifications + historique --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Certifications --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700 text-sm">Certifications & habilitations</h3>
                    <a href="{{ route('ouvriers.edit', $ouvrier) }}" class="text-xs text-blue-500 hover:text-blue-700">Gérer →</a>
                </div>
                @if($ouvrier->certifications->isEmpty())
                    <p class="px-5 py-4 text-sm text-gray-400 text-center">Aucune certification enregistrée.</p>
                @else
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">Certification</th>
                            <th class="px-4 py-2 text-left">Obtention</th>
                            <th class="px-4 py-2 text-left">Expiration</th>
                            <th class="px-4 py-2 text-left">Organisme</th>
                            <th class="px-4 py-2 text-left">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($ouvrier->certifications as $cert)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium text-gray-800">{{ $cert->libelle_type }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $cert->date_obtention->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $cert->date_expiration ? $cert->date_expiration->format('d/m/Y') : '—' }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $cert->organisme ?: '—' }}</td>
                            <td class="px-4 py-2">
                                @if($cert->est_expiree)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 font-medium">Expirée</span>
                                @elseif($cert->expire_bientot)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700 font-medium">À renouveler</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 font-medium">Valide</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            {{-- Bradford Factor & résumé absences (uniquement pour personnel planifiable) --}}
            @if($ouvrier->est_planifiable && ($resumeAbsences->isNotEmpty() || $bradfordFactor > 0))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-700 text-sm">Absences {{ now()->year }}</h3>
                    @php
                        $bfColor = $bradfordFactor < 50 ? 'text-green-700 bg-green-50' : ($bradfordFactor < 200 ? 'text-amber-700 bg-amber-50' : 'text-red-700 bg-red-100');
                    @endphp
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">Bradford Factor</span>
                        <span class="px-2 py-0.5 rounded-full text-sm font-bold {{ $bfColor }}">{{ $bradfordFactor }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    @foreach($resumeAbsences as $type => $info)
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                        <span class="text-gray-600">{{ $info['libelle'] }}</span>
                        <span class="font-medium text-gray-800">{{ $info['count'] }} ép. · {{ $info['jours'] }}j</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Derniers pointages (uniquement pour personnel planifiable) --}}
            @if($ouvrier->est_planifiable)
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
            @endif

        </div>
    </div>
</x-app-layout>
