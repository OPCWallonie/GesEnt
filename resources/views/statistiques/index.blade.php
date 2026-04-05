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

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">CA facturé {{ $annee }}</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($totalVentes, 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">TTC toutes factures</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Encaissé {{ $annee }}</div>
            <div class="text-2xl font-bold text-green-700">{{ number_format($totalEncaisse, 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">Paiements reçus</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Achats {{ $annee }}</div>
            <div class="text-2xl font-bold text-orange-600">{{ number_format($totalAchats, 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">Factures fournisseurs</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Marge brute {{ $annee }}</div>
            @php $marge = $totalVentes - $totalAchats; $tauxMarge = $totalVentes > 0 ? ($marge / $totalVentes) * 100 : 0; @endphp
            <div class="text-2xl font-bold {{ $marge >= 0 ? 'text-blue-700' : 'text-red-600' }}">{{ number_format($marge, 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">{{ number_format($tauxMarge, 1) }}% du CA</div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
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
    </div>

    {{-- Graphique CA --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-700 mb-4">Ventes vs Achats {{ $annee }} (TTC)</h3>
        <canvas id="chartCA" height="80"></canvas>
    </div>

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
    const ctx = document.getElementById('chartCA').getContext('2d');
    new Chart(ctx, {
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
    </script>
</x-app-layout>
