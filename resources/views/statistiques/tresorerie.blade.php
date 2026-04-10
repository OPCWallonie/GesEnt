<x-app-layout>
    <x-slot name="header">Projection de trésorerie</x-slot>

    @include('statistiques.partials.nav')

    @php
        $totalEntrees = $semaines->sum('entrees');
        $totalSorties = $semaines->sum('sorties');
        $soldeFinal   = $semaines->last()['solde_cumule'] ?? 0;
        $semainesNegatives = $semaines->where('solde_cumule', '<', 0)->count();
    @endphp

    {{-- Alerte --}}
    @if($semainesNegatives > 0)
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
            <strong>Attention :</strong> {{ $semainesNegatives }} semaine(s) présente(nt) un solde cumulé négatif dans les 12 prochaines semaines.
        </div>
    @endif

    {{-- KPI résumé --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-5">
            <div class="text-xs text-green-600 uppercase font-medium mb-1">Entrées attendues (12 sem.)</div>
            <div class="text-2xl font-bold text-green-700">{{ number_format($totalEntrees, 0, ',', ' ') }} €</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-5">
            <div class="text-xs text-red-600 uppercase font-medium mb-1">Sorties prévues (12 sem.)</div>
            <div class="text-2xl font-bold text-red-600">{{ number_format($totalSorties, 0, ',', ' ') }} €</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border {{ $soldeFinal >= 0 ? 'border-blue-200' : 'border-red-300' }} p-5">
            <div class="text-xs {{ $soldeFinal >= 0 ? 'text-blue-600' : 'text-red-700' }} uppercase font-medium mb-1">Solde cumulé fin S12</div>
            <div class="text-2xl font-bold {{ $soldeFinal >= 0 ? 'text-blue-700' : 'text-red-700' }}">{{ number_format($soldeFinal, 0, ',', ' ') }} €</div>
        </div>
    </div>

    {{-- Graphique --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-700 mb-4 text-sm">Flux de trésorerie — 12 prochaines semaines</h3>
        <canvas id="chartTresorerie" height="100"></canvas>
    </div>

    {{-- Tableau détaillé --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-700 text-sm">Détail par semaine</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-100">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Semaine</th>
                        <th class="px-4 py-2 text-left text-gray-400">Période</th>
                        <th class="px-4 py-2 text-right text-green-600">Entrées</th>
                        <th class="px-4 py-2 text-right text-red-600">Sorties</th>
                        <th class="px-4 py-2 text-right">Solde</th>
                        <th class="px-4 py-2 text-right">Cumulé</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($semaines as $s)
                    @php $negatif = $s['solde_cumule'] < 0; @endphp
                    <tr class="{{ $negatif ? 'bg-red-50' : 'hover:bg-gray-50' }}">
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $s['label'] }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $s['debut'] }} – {{ $s['fin'] }}</td>
                        <td class="px-4 py-3 text-right text-green-600 font-medium">
                            {{ $s['entrees'] > 0 ? number_format($s['entrees'], 0, ',', ' ').' €' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-red-600">
                            {{ $s['sorties'] > 0 ? number_format($s['sorties'], 0, ',', ' ').' €' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right {{ $s['solde'] >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                            {{ number_format($s['solde'], 0, ',', ' ') }} €
                        </td>
                        <td class="px-4 py-3 text-right font-bold {{ $negatif ? 'text-red-700' : 'text-blue-700' }}">
                            {{ number_format($s['solde_cumule'], 0, ',', ' ') }} €
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
    const ctx = document.getElementById('chartTresorerie').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($semaines->pluck('label')),
            datasets: [
                {
                    label: 'Entrées attendues',
                    data: @json($semaines->pluck('entrees')),
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1,
                    borderRadius: 4,
                    order: 2,
                },
                {
                    label: 'Sorties prévues',
                    data: @json($semaines->pluck('sorties')),
                    backgroundColor: 'rgba(239, 68, 68, 0.7)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1,
                    borderRadius: 4,
                    order: 2,
                },
                {
                    label: 'Solde cumulé',
                    data: @json($semaines->pluck('solde_cumule')),
                    type: 'line',
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    tension: 0.3,
                    fill: false,
                    order: 1,
                    yAxisID: 'y',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label + ': ' + new Intl.NumberFormat('fr-BE', {style:'currency', currency:'EUR', maximumFractionDigits:0}).format(ctx.raw)
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: val => new Intl.NumberFormat('fr-BE', {style:'currency', currency:'EUR', maximumFractionDigits:0}).format(val)
                    }
                }
            }
        }
    });
    </script>
</x-app-layout>
