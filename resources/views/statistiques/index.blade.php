<x-app-layout>
    <x-slot name="header">Statistiques</x-slot>
    <x-slot name="actions">
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Année :</label>
            <select name="annee" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm text-sm">
                @foreach($annees as $a)
                    <option value="{{ $a }}" @selected($a == $annee)>{{ $a }}</option>
                @endforeach
            </select>
        </form>
    </x-slot>

    @include('statistiques.partials.nav')

    {{-- KPIs principaux avec variation N-1 --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        @php
            $variationVentes = $ventesN1 > 0 ? (($totalVentes - $ventesN1) / $ventesN1) * 100 : null;
            $variationAchats = $achatsN1 > 0 ? (($totalAchats - $achatsN1) / $achatsN1) * 100 : null;
            $variationEncaisse = $encaisseN1 > 0 ? (($totalEncaisse - $encaisseN1) / $encaisseN1) * 100 : null;
            $margeN = $totalVentes - $totalAchats;
            $tauxMargeN = $totalVentes > 0 ? ($margeN / $totalVentes) * 100 : 0;
            $variationMarge = $margeN1 != 0 ? (($margeN - $margeN1) / abs($margeN1)) * 100 : null;
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">CA facturé {{ $annee }}</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($totalVentes, 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">TTC toutes factures</div>
            @include('statistiques.partials._variation', ['variation' => $variationVentes, 'reference' => $ventesN1, 'anneeN1' => $annee - 1])
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Encaissé {{ $annee }}</div>
            <div class="text-2xl font-bold text-green-700">{{ number_format($totalEncaisse, 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">Paiements reçus</div>
            @include('statistiques.partials._variation', ['variation' => $variationEncaisse, 'reference' => $encaisseN1, 'anneeN1' => $annee - 1])
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Achats {{ $annee }}</div>
            <div class="text-2xl font-bold text-orange-600">{{ number_format($totalAchats, 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">Factures fournisseurs</div>
            @include('statistiques.partials._variation', ['variation' => $variationAchats, 'reference' => $achatsN1, 'anneeN1' => $annee - 1, 'inverser' => true])
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Marge brute {{ $annee }}</div>
            <div class="text-2xl font-bold {{ $margeN >= 0 ? 'text-blue-700' : 'text-red-600' }}">{{ number_format($margeN, 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">{{ number_format($tauxMargeN, 1) }}% du CA</div>
            @include('statistiques.partials._variation', ['variation' => $variationMarge, 'reference' => $margeN1, 'anneeN1' => $annee - 1])
        </div>
    </div>

    {{-- KPIs secondaires --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Devis émis</div>
            <div class="text-3xl font-bold text-gray-800">{{ $nbDevis }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">BDC signés</div>
            <div class="text-3xl font-bold text-gray-800">{{ $nbBdc }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Taux conversion</div>
            <div class="text-3xl font-bold {{ $tauxConversion >= 50 ? 'text-green-700' : 'text-orange-600' }}">{{ $tauxConversion }}%</div>
            <div class="text-xs text-gray-400 mt-1">Devis → BDC</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">En retard</div>
            <div class="text-3xl font-bold {{ $totalEnRetard > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ number_format($totalEnRetard, 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">{{ $facturesEnRetard->count() }} facture(s)</div>
        </div>
        {{-- DSO --}}
        <div class="bg-white rounded-xl shadow-sm border {{ $dso > 60 ? 'border-red-200' : ($dso > 45 ? 'border-orange-200' : 'border-green-200') }} p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">DSO</div>
            <div class="text-3xl font-bold {{ $dso > 60 ? 'text-red-600' : ($dso > 45 ? 'text-orange-600' : 'text-green-700') }}">
                {{ $dso }}j
            </div>
            <div class="text-xs text-gray-400 mt-1">
                @if($dsoReel)Délai réel : {{ $dsoReel }}j@else Délai moyen théorique @endif
            </div>
        </div>
    </div>

    {{-- Graphique CA avec toggle N-1 --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
         x-data="{ showN1: false }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-700">Ventes vs Achats {{ $annee }} (TTC)</h3>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" x-model="showN1" @change="toggleN1()" class="rounded border-gray-300 text-blue-600">
                <span class="text-gray-600">Comparer avec {{ $annee - 1 }}</span>
            </label>
        </div>
        <canvas id="chartCA" height="80"></canvas>
    </div>

    {{-- Funnel de conversion --}}
    @if($devisEmis > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-700 mb-5">Funnel de conversion {{ $annee }}</h3>
        @php
            $funnelSteps = [
                ['label' => 'Devis émis',   'count' => $devisEmis,       'montant' => $montantDevis,    'color' => 'bg-blue-500'],
                ['label' => 'Devis validés', 'count' => $devisAcceptes,  'montant' => $montantAcceptes, 'color' => 'bg-indigo-500'],
                ['label' => 'BDC signés',    'count' => $bdcGeneres,     'montant' => $montantBdc,      'color' => 'bg-purple-500'],
                ['label' => 'Facturés',      'count' => $facturesEmises, 'montant' => $montantFacture,  'color' => 'bg-violet-500'],
                ['label' => 'Encaissés',     'count' => $facturesPayees, 'montant' => $montantEncaisse, 'color' => 'bg-green-500'],
            ];
        @endphp
        <div class="flex items-end gap-1">
            @foreach($funnelSteps as $step)
            @php $pct = $devisEmis > 0 ? round(($step['count'] / $devisEmis) * 100) : 0; @endphp
            <div class="flex-1 flex flex-col items-center gap-1.5">
                <div class="text-xs font-medium text-gray-600 text-center">{{ $step['label'] }}</div>
                <div class="w-full {{ $step['color'] }} rounded-t transition-all flex items-end justify-center"
                     style="height: {{ max(20, $pct * 1.5) }}px;">
                    <span class="text-white text-xs font-bold pb-1">{{ $step['count'] }}</span>
                </div>
                <div class="text-center">
                    <div class="text-xs font-bold text-gray-700">{{ $pct }}%</div>
                    <div class="text-xs text-gray-400">{{ number_format($step['montant'], 0, ',', ' ') }} €</div>
                </div>
            </div>
            @if(!$loop->last)
                <div class="mb-8 text-gray-300 text-lg">→</div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Top clients --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700">Top 5 clients — CA {{ $annee }}</h3>
            </div>
            @if($topClients->count())
                @php $maxClient = $topClients->first()->total; @endphp
                <div class="p-5 space-y-3">
                    @foreach($topClients as $client)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-800">{{ $client->nom }}</span>
                                <span class="text-gray-600">{{ number_format($client->total, 0, ',', ' ') }} €</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $maxClient > 0 ? ($client->total / $maxClient * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="px-5 py-8 text-sm text-gray-400 text-center">Aucune donnée.</p>
            @endif
        </div>

        {{-- Achats par catégorie --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700">Achats par catégorie — {{ $annee }}</h3>
            </div>
            @if($achatsParCategorie->count())
                <div class="p-5 space-y-3">
                    @php
                        $maxAchat = $achatsParCategorie->first()->total;
                        $colors = ['materiel' => 'bg-blue-500', 'sous_traitance' => 'bg-purple-500', 'frais_generaux' => 'bg-gray-500', 'divers' => 'bg-yellow-500'];
                    @endphp
                    @foreach($achatsParCategorie as $cat)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-800">{{ \App\Models\FactureAchat::$categories[$cat->categorie] ?? $cat->categorie }}</span>
                                <span class="text-gray-600">{{ number_format($cat->total, 0, ',', ' ') }} €</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="{{ $colors[$cat->categorie] ?? 'bg-gray-400' }} h-2 rounded-full" style="width: {{ $maxAchat > 0 ? ($cat->total / $maxAchat * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="px-5 py-8 text-sm text-gray-400 text-center">Aucun achat enregistré.</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top fournisseurs --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700">Top 5 fournisseurs — {{ $annee }}</h3>
            </div>
            @if($topFournisseurs->count())
                @php $maxF = $topFournisseurs->first()->total; @endphp
                <div class="p-5 space-y-3">
                    @foreach($topFournisseurs as $f)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-800">{{ $f->nom }}</span>
                                <span class="text-gray-600">{{ number_format($f->total, 0, ',', ' ') }} €</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-orange-400 h-2 rounded-full" style="width: {{ $maxF > 0 ? ($f->total / $maxF * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="px-5 py-8 text-sm text-gray-400 text-center">Aucune donnée.</p>
            @endif
        </div>

        {{-- Factures en retard --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-gray-700">Factures en retard</h3>
                @if($totalEnRetard > 0)
                    <span class="text-sm font-bold text-red-600">{{ number_format($totalEnRetard, 0, ',', ' ') }} €</span>
                @endif
            </div>
            @if($facturesEnRetard->count())
                @foreach($facturesEnRetard as $f)
                    <div class="flex items-center justify-between px-5 py-3 border-b last:border-0 hover:bg-red-50">
                        <div>
                            <a href="{{ route('factures.show', $f) }}" class="font-mono text-sm font-medium text-gray-800 hover:text-blue-600">{{ $f->numero }}</a>
                            <div class="text-xs text-gray-400">{{ $f->client->nom }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-red-600">{{ number_format($f->montant_net_a_payer, 0, ',', ' ') }} €</div>
                            <div class="text-xs text-gray-400">depuis {{ $f->date_echeance->diffForHumans() }}</div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="px-5 py-8 text-sm text-gray-400 text-center">Aucune facture en retard.</p>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
    const caVentes   = @json($caVentes);
    const caVentesN1 = @json($caVentesN1);
    const anneeN1    = {{ $annee - 1 }};

    const ctx = document.getElementById('chartCA').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($moisLabels),
            datasets: [
                {
                    label: 'Ventes TTC',
                    data: @json($caVentes),
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Achats TTC',
                    data: @json($caAchats),
                    backgroundColor: 'rgba(249, 115, 22, 0.7)',
                    borderColor: 'rgb(249, 115, 22)',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Marge brute',
                    data: @json($caMarges),
                    type: 'line',
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    tension: 0.3,
                    fill: false,
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
                        label: ctx => ctx.dataset.label + ': ' + new Intl.NumberFormat('fr-BE', {style:'currency', currency:'EUR'}).format(ctx.raw)
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

    function toggleN1() {
        const existing = chart.data.datasets.findIndex(d => d.label.includes(anneeN1));
        if (existing >= 0) {
            chart.data.datasets.splice(existing, 1);
        } else {
            chart.data.datasets.push({
                label: 'Ventes ' + anneeN1,
                data: caVentesN1,
                type: 'line',
                borderColor: 'rgba(99, 102, 241, 0.8)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 2,
                borderDash: [5, 5],
                pointRadius: 3,
                tension: 0.3,
                fill: false,
            });
        }
        chart.update();
    }
    </script>
</x-app-layout>
