<x-app-layout>
    <x-slot name="header">Rentabilité chantiers</x-slot>

    @include('statistiques.partials.nav')

    @php
        $totalVentes     = $chantiers->sum('ventes');
        $totalAchats     = $chantiers->sum('achats');
        $totalMo         = $chantiers->sum('cout_mo');
        $totalMarge      = $chantiers->sum('marge');
        $totalMargeReelle = $chantiers->sum('marge_reelle');
        $tauxGlobal      = $totalVentes > 0 ? ($totalMarge / $totalVentes) * 100 : 0;
        $tauxReelGlobal  = $totalVentes > 0 ? ($totalMargeReelle / $totalVentes) * 100 : 0;
    @endphp

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Chantiers actifs</div>
            <div class="text-3xl font-bold text-gray-800">{{ $chantiers->count() }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">CA total</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($totalVentes, 0, ',', ' ') }} €</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Marge globale</div>
            <div class="text-2xl font-bold {{ $totalMarge >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($totalMarge, 0, ',', ' ') }} €</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border {{ $tauxGlobal >= 25 ? 'border-green-200' : ($tauxGlobal >= 10 ? 'border-orange-200' : 'border-red-200') }} p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Taux moyen</div>
            <div class="text-2xl font-bold {{ $tauxGlobal >= 25 ? 'text-green-700' : ($tauxGlobal >= 10 ? 'text-orange-600' : 'text-red-600') }}">
                {{ number_format($tauxGlobal, 1) }}%
            </div>
        </div>
    </div>

    {{-- Graphique bubble --}}
    @if($chantiers->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-700 mb-4 text-sm">CA vs Taux de marge (taille = marge absolue)</h3>
        <canvas id="chartBubble" height="80"></canvas>
    </div>
    @endif

    {{-- Tableau classement --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
         x-data="{ tri: 'marge', dir: 'desc' }">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-700 text-sm">Classement par rentabilité</h3>
        </div>

        @if($chantiers->isEmpty())
            <p class="px-5 py-8 text-sm text-gray-400 text-center">Aucun chantier avec données financières.</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-100">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left w-6">#</th>
                        <th class="px-4 py-2 text-left">Chantier</th>
                        <th class="px-4 py-2 text-left">Client</th>
                        <th class="px-4 py-2 text-right">Ventes</th>
                        <th class="px-4 py-2 text-right">Achats</th>
                        <th class="px-4 py-2 text-right">MO</th>
                        <th class="px-4 py-2 text-right">Marge brute</th>
                        <th class="px-4 py-2 text-right">Marge réelle</th>
                        <th class="px-4 py-2 text-right">Taux réel</th>
                        <th class="px-4 py-2 text-center">Avancement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($chantiers as $i => $data)
                    @php
                        $tauxR   = $data['taux_marge_reelle'];
                        $couleur = $tauxR === null ? 'text-gray-400' : ($tauxR >= 25 ? 'text-green-600' : ($tauxR >= 10 ? 'text-orange-600' : 'text-red-600'));
                        $bg      = $tauxR === null ? '' : ($tauxR >= 25 ? 'bg-green-50' : ($tauxR >= 10 ? 'bg-orange-50' : 'bg-red-50'));
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium">
                            <a href="{{ route('chantiers.show', $data['chantier']) }}" class="text-gray-800 hover:text-blue-600">
                                {{ $data['chantier']->nom }}
                            </a>
                            @if($data['nb_factures'] > 0)
                                <span class="text-xs text-gray-400 ml-1">({{ $data['nb_factures'] }} fact.)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $data['chantier']->client?->nom ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-800">{{ number_format($data['ventes'], 0, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right text-orange-600">{{ number_format($data['achats'], 0, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right {{ $data['cout_mo'] > 0 ? 'text-orange-500' : 'text-gray-300' }}">
                            {{ $data['cout_mo'] > 0 ? number_format($data['cout_mo'], 0, ',', ' ').' €' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium {{ $data['marge'] >= 0 ? 'text-green-700' : 'text-red-600' }}">
                            {{ number_format($data['marge'], 0, ',', ' ') }} €
                        </td>
                        <td class="px-4 py-3 text-right font-bold {{ $data['marge_reelle'] >= 0 ? 'text-green-700' : 'text-red-600' }}">
                            {{ number_format($data['marge_reelle'], 0, ',', ' ') }} €
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($tauxR !== null)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $bg }} {{ $couleur }}">
                                    {{ number_format($tauxR, 1) }}%
                                </span>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($data['avancement'] > 0)
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ min($data['avancement'], 100) }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 w-8 text-right">{{ $data['avancement'] }}%</span>
                                </div>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 text-sm font-bold border-t-2 border-gray-200">
                    <tr>
                        <td class="px-4 py-3" colspan="3">TOTAL</td>
                        <td class="px-4 py-3 text-right">{{ number_format($totalVentes, 0, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right text-orange-600">{{ number_format($totalAchats, 0, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right {{ $totalMo > 0 ? 'text-orange-500' : 'text-gray-300' }}">
                            {{ $totalMo > 0 ? number_format($totalMo, 0, ',', ' ').' €' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right {{ $totalMarge >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($totalMarge, 0, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right {{ $totalMargeReelle >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($totalMargeReelle, 0, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right {{ $tauxReelGlobal >= 25 ? 'text-green-600' : ($tauxReelGlobal >= 10 ? 'text-orange-600' : 'text-red-600') }}">
                            {{ number_format($tauxReelGlobal, 1) }}%
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>

    @if($chantiers->isNotEmpty())
    @php
    $bubbleData = $chantiers->map(fn($c) => [
        'x'    => round($c['ventes'], 2),
        'y'    => $c['taux_marge'] !== null ? round($c['taux_marge'], 1) : 0,
        'r'    => max(5, min(30, sqrt(abs($c['marge'])) / 5)),
        'nom'  => $c['chantier']->nom,
        'marge' => $c['marge'],
        'taux' => $c['taux_marge'],
    ])->values();
    @endphp
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
    @if($chantiers->isNotEmpty())
    const bubbleData = @json($bubbleData);

    const colors = bubbleData.map(d => d.taux >= 25 ? 'rgba(34,197,94,0.6)' : (d.taux >= 10 ? 'rgba(249,115,22,0.6)' : 'rgba(239,68,68,0.6)'));

    const ctx = document.getElementById('chartBubble').getContext('2d');
    new Chart(ctx, {
        type: 'bubble',
        data: {
            datasets: [{
                label: 'Chantiers',
                data: bubbleData,
                backgroundColor: colors,
                borderColor: colors.map(c => c.replace('0.6', '1')),
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const d = ctx.raw;
                            return [
                                d.nom,
                                'CA : ' + new Intl.NumberFormat('fr-BE', {style:'currency', currency:'EUR', maximumFractionDigits:0}).format(d.x),
                                'Marge : ' + (d.taux !== null ? d.taux.toFixed(1)+'%' : '—'),
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: { display: true, text: "Chiffre d'affaires (€)" },
                    ticks: { callback: v => new Intl.NumberFormat('fr-BE', {style:'currency', currency:'EUR', maximumFractionDigits:0}).format(v) }
                },
                y: {
                    title: { display: true, text: 'Taux de marge (%)' },
                    ticks: { callback: v => v + '%' }
                }
            }
        }
    });
    @endif
    </script>
</x-app-layout>
