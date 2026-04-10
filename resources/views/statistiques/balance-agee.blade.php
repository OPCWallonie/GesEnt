<x-app-layout>
    <x-slot name="header">Balance âgée</x-slot>

    @include('statistiques.partials.nav')

    @php
        $totaux = [
            'non_echues' => $tranches['non_echues']->sum('montant_net_a_payer'),
            '0_30'       => $tranches['0_30']->sum('montant_net_a_payer'),
            '31_60'      => $tranches['31_60']->sum('montant_net_a_payer'),
            '61_90'      => $tranches['61_90']->sum('montant_net_a_payer'),
            'plus_90'    => $tranches['plus_90']->sum('montant_net_a_payer'),
        ];
        $totalGeneral = array_sum($totaux);
    @endphp

    {{-- Cartes résumé --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-4">
            <div class="text-xs font-medium text-green-600 uppercase mb-1">Non échues</div>
            <div class="text-xl font-bold text-green-700">{{ number_format($totaux['non_echues'], 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-0.5">{{ $tranches['non_echues']->count() }} facture(s)</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-yellow-200 p-4">
            <div class="text-xs font-medium text-yellow-600 uppercase mb-1">0 – 30 jours</div>
            <div class="text-xl font-bold text-yellow-700">{{ number_format($totaux['0_30'], 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-0.5">{{ $tranches['0_30']->count() }} facture(s)</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-4">
            <div class="text-xs font-medium text-orange-600 uppercase mb-1">31 – 60 jours</div>
            <div class="text-xl font-bold text-orange-600">{{ number_format($totaux['31_60'], 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-0.5">{{ $tranches['31_60']->count() }} facture(s)</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-4">
            <div class="text-xs font-medium text-red-600 uppercase mb-1">61 – 90 jours</div>
            <div class="text-xl font-bold text-red-600">{{ number_format($totaux['61_90'], 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-0.5">{{ $tranches['61_90']->count() }} facture(s)</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-red-300 p-4">
            <div class="text-xs font-medium text-red-800 uppercase mb-1">> 90 jours</div>
            <div class="text-xl font-bold text-red-800">{{ number_format($totaux['plus_90'], 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-0.5">{{ $tranches['plus_90']->count() }} facture(s)</div>
        </div>
    </div>

    {{-- Graphique barres empilées --}}
    @if($totalGeneral > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h3 class="font-semibold text-gray-700 mb-4 text-sm">Répartition des créances par ancienneté</h3>
        <div class="h-8 w-full rounded-full overflow-hidden flex">
            @foreach([
                ['non_echues', 'bg-green-400',  $totaux['non_echues']],
                ['0_30',       'bg-yellow-400', $totaux['0_30']],
                ['31_60',      'bg-orange-400', $totaux['31_60']],
                ['61_90',      'bg-red-400',    $totaux['61_90']],
                ['plus_90',    'bg-red-700',    $totaux['plus_90']],
            ] as [$key, $color, $montant])
                @if($montant > 0)
                    <div class="{{ $color }} transition-all"
                         style="width: {{ ($montant / $totalGeneral) * 100 }}%"
                         title="{{ number_format($montant, 0, ',', ' ') }} €"></div>
                @endif
            @endforeach
        </div>
        <div class="flex flex-wrap gap-4 mt-3 text-xs text-gray-500">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-green-400 inline-block"></span> Non échues</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-yellow-400 inline-block"></span> 0-30j</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-orange-400 inline-block"></span> 31-60j</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-red-400 inline-block"></span> 61-90j</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-red-700 inline-block"></span> >90j</span>
        </div>
    </div>
    @endif

    {{-- Tableau par client --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-700">Créances par client</h3>
            @if($totalGeneral > 0)
                <span class="text-sm font-bold text-gray-700">Total : {{ number_format($totalGeneral, 0, ',', ' ') }} €</span>
            @endif
        </div>

        @if($parClient->isEmpty())
            <p class="px-5 py-8 text-sm text-gray-400 text-center">Aucune créance impayée.</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-100">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Client</th>
                        <th class="px-4 py-2 text-right text-green-600">Non échues</th>
                        <th class="px-4 py-2 text-right text-yellow-600">0–30j</th>
                        <th class="px-4 py-2 text-right text-orange-600">31–60j</th>
                        <th class="px-4 py-2 text-right text-red-600">61–90j</th>
                        <th class="px-4 py-2 text-right text-red-800">>90j</th>
                        <th class="px-4 py-2 text-right font-bold">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" x-data="{ ouvert: null }">
                    @foreach($parClient as $clientId => $data)
                    <tr class="hover:bg-gray-50 cursor-pointer"
                        @click="ouvert = ouvert === {{ $clientId }} ? null : {{ $clientId }}">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 transition-transform"
                                     :class="ouvert === {{ $clientId }} ? 'rotate-90' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                @if($data['client'])
                                    <a href="{{ route('clients.show', $data['client']) }}"
                                       @click.stop
                                       class="hover:text-blue-600">{{ $data['client']->nom }}</a>
                                @else
                                    <span class="text-gray-400">Client inconnu</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right {{ $data['non_echues'] > 0 ? 'text-green-600' : 'text-gray-300' }}">
                            {{ $data['non_echues'] > 0 ? number_format($data['non_echues'], 0, ',', ' ').' €' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right {{ $data['0_30'] > 0 ? 'text-yellow-700' : 'text-gray-300' }}">
                            {{ $data['0_30'] > 0 ? number_format($data['0_30'], 0, ',', ' ').' €' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right {{ $data['31_60'] > 0 ? 'text-orange-600 font-medium' : 'text-gray-300' }}">
                            {{ $data['31_60'] > 0 ? number_format($data['31_60'], 0, ',', ' ').' €' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right {{ $data['61_90'] > 0 ? 'text-red-600 font-bold' : 'text-gray-300' }}">
                            {{ $data['61_90'] > 0 ? number_format($data['61_90'], 0, ',', ' ').' €' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right {{ $data['plus_90'] > 0 ? 'text-red-800 font-bold' : 'text-gray-300' }}">
                            {{ $data['plus_90'] > 0 ? number_format($data['plus_90'], 0, ',', ' ').' €' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900">{{ number_format($data['total'], 0, ',', ' ') }} €</td>
                    </tr>

                    {{-- Détail factures du client (pliable) --}}
                    <tr x-show="ouvert === {{ $clientId }}" x-cloak>
                        <td colspan="7" class="px-0 py-0 bg-gray-50">
                            <table class="min-w-full text-xs">
                                <thead class="text-gray-400 border-b border-gray-200">
                                    <tr>
                                        <th class="px-8 py-2 text-left">Numéro</th>
                                        <th class="px-4 py-2 text-left">Émise</th>
                                        <th class="px-4 py-2 text-left">Échéance</th>
                                        <th class="px-4 py-2 text-right">Retard</th>
                                        <th class="px-4 py-2 text-right">Montant</th>
                                        <th class="px-4 py-2 text-right">Relances</th>
                                        <th class="px-4 py-2 text-right">Dernière relance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($data['factures'] as $facture)
                                    @php $joursR = $facture->date_echeance->isPast() ? (int) $facture->date_echeance->diffInDays(now()) : 0; @endphp
                                    <tr class="hover:bg-white">
                                        <td class="px-8 py-2">
                                            <a href="{{ route('factures.show', $facture) }}"
                                               class="font-mono text-blue-600 hover:underline">{{ $facture->numero }}</a>
                                        </td>
                                        <td class="px-4 py-2 text-gray-500">{{ $facture->date_document->format('d/m/Y') }}</td>
                                        <td class="px-4 py-2 {{ $facture->date_echeance->isPast() ? 'text-red-600' : 'text-gray-500' }}">
                                            {{ $facture->date_echeance->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-2 text-right {{ $joursR > 60 ? 'text-red-700 font-bold' : ($joursR > 0 ? 'text-orange-600' : 'text-green-600') }}">
                                            {{ $joursR > 0 ? $joursR.'j' : '—' }}
                                        </td>
                                        <td class="px-4 py-2 text-right font-medium">{{ number_format($facture->montant_net_a_payer, 0, ',', ' ') }} €</td>
                                        <td class="px-4 py-2 text-right {{ $facture->nb_relances > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                            {{ $facture->nb_relances }}
                                        </td>
                                        <td class="px-4 py-2 text-right text-gray-400">
                                            {{ $facture->derniere_relance_at?->format('d/m/Y') ?? '—' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    @endforeach

                    {{-- Total général --}}
                    <tr class="bg-gray-50 font-bold text-sm">
                        <td class="px-4 py-3">TOTAL</td>
                        <td class="px-4 py-3 text-right text-green-600">{{ number_format($totaux['non_echues'], 0, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right text-yellow-700">{{ number_format($totaux['0_30'], 0, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right text-orange-600">{{ number_format($totaux['31_60'], 0, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right text-red-600">{{ number_format($totaux['61_90'], 0, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right text-red-800">{{ number_format($totaux['plus_90'], 0, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ number_format($totalGeneral, 0, ',', ' ') }} €</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </div>

</x-app-layout>
