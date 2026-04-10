<x-app-layout>
    <x-slot name="header">Tableau de bord</x-slot>

    {{-- Alertes --}}
    @if($facturesEnRetard->count() > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="font-semibold text-red-800">{{ $facturesEnRetard->count() }} facture(s) client en retard</span>
            </div>
            <div class="space-y-1">
                @foreach($facturesEnRetard as $f)
                    <div class="flex items-center justify-between text-sm text-red-700">
                        <a href="{{ route('factures.show', $f) }}" class="hover:underline font-mono">{{ $f->numero }}</a>
                        <span class="text-gray-500">{{ $f->client->nom }}</span>
                        <span class="font-semibold">{{ number_format($f->montant_net_a_payer, 2, ',', ' ') }} €</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($achatsEnRetard->count() > 0)
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span class="font-semibold text-orange-800">{{ $achatsEnRetard->count() }} facture(s) fournisseur en retard</span>
            </div>
            <div class="space-y-1">
                @foreach($achatsEnRetard as $fa)
                    <div class="flex items-center justify-between text-sm text-orange-700">
                        <a href="{{ route('factures-achat.show', $fa) }}" class="hover:underline font-mono">{{ $fa->numero }}</a>
                        <span class="text-gray-500">{{ $fa->fournisseur->nom }}</span>
                        <span class="font-semibold">{{ number_format($fa->montant_ttc, 2, ',', ' ') }} €</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($devisExpirantBientot->count() > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-semibold text-amber-800">{{ $devisExpirantBientot->count() }} devis expirant dans 7 jours</span>
            </div>
            <div class="space-y-1">
                @foreach($devisExpirantBientot as $d)
                    <div class="flex items-center justify-between text-sm text-amber-700">
                        <a href="{{ route('devis.show', $d) }}" class="hover:underline font-mono">{{ $d->numero }}</a>
                        <span class="text-gray-500">{{ $d->client->nom }}</span>
                        <span>expire le {{ $d->date_validite->format('d/m/Y') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- KPIs du mois --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">CA du mois (TTC)</div>
            <div class="text-2xl font-bold text-blue-700">{{ number_format($stats['ca_mois'], 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">Factures émises</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Achats du mois</div>
            <div class="text-2xl font-bold text-orange-600">{{ number_format($stats['achats_mois'], 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">Factures fournisseurs</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Marge brute mois</div>
            @php $marge = $stats['marge_mois']; @endphp
            <div class="text-2xl font-bold {{ $marge >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($marge, 0, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">
                @if($stats['ca_mois'] > 0) {{ number_format(($marge / $stats['ca_mois']) * 100, 1) }}% du CA @endif
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">À encaisser</div>
            <div class="text-2xl font-bold text-gray-700">{{ number_format($stats['a_encaisser'], 0, ',', ' ') }} €</div>
            <div class="text-xs {{ $stats['a_payer_fournisseurs'] > 0 ? 'text-orange-500' : 'text-gray-400' }} mt-1">
                à payer: {{ number_format($stats['a_payer_fournisseurs'], 0, ',', ' ') }} €
            </div>
        </div>
    </div>

    {{-- Card MO semaine --}}
    @if($nbOuvriersActifs > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-4 flex items-center gap-6">
        <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <div class="text-xs text-gray-400 uppercase font-medium mb-0.5">Main d'œuvre — semaine en cours</div>
            <div class="text-xl font-bold text-gray-800">
                {{ $nbOuvriersPlanifies }} / {{ $nbOuvriersActifs }} ouvriers planifiés
                <span class="text-base font-medium text-orange-600 ml-3">{{ number_format($moSemaine, 0, ',', ' ') }} €</span>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('pointages.index') }}" class="text-sm text-blue-600 hover:text-blue-800 border border-blue-200 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition">
                Planning
            </a>
            <a href="{{ route('ouvriers.index') }}" class="text-sm text-gray-600 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">
                Ouvriers
            </a>
        </div>
    </div>
    @endif

    {{-- Compteurs activité --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="text-3xl font-bold text-gray-800">{{ $stats['devis_en_attente'] }}</div>
                <div class="text-sm text-gray-400">Devis en cours</div>
                <a href="{{ route('devis.index') }}" class="text-xs text-blue-500 hover:underline">Voir →</a>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <div>
                <div class="text-3xl font-bold text-gray-800">{{ $stats['bdc_en_cours'] }}</div>
                <div class="text-sm text-gray-400">BDC actifs</div>
                <a href="{{ route('bons-commande.index') }}" class="text-xs text-blue-500 hover:underline">Voir →</a>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <div class="text-3xl font-bold text-gray-800">{{ $stats['factures_en_attente'] }}</div>
                <div class="text-sm text-gray-400">Factures à traiter</div>
                <a href="{{ route('factures.index') }}" class="text-xs text-blue-500 hover:underline">Voir →</a>
            </div>
        </div>
    </div>

    {{-- Chantiers actifs --}}
    @if($chantiersActifs->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-700">Chantiers actifs</h3>
            <a href="{{ route('chantiers.index', ['statut' => 'actif']) }}" class="text-xs text-blue-500 hover:underline">Tout voir</a>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($chantiersActifs as $c)
            <a href="{{ route('chantiers.show', $c) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-900 truncate">{{ $c->nom }}</span>
                        <span class="text-xs text-gray-400 flex-shrink-0 ml-3">{{ $c->client->nom }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full {{ $c->avancement >= 100 ? 'bg-green-500' : ($c->avancement >= 50 ? 'bg-blue-500' : 'bg-amber-400') }}"
                                 style="width: {{ $c->avancement }}%"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-500 w-8 text-right">{{ $c->avancement }}%</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Derniers devis --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-700">Derniers devis</h3>
                <a href="{{ route('devis.index') }}" class="text-xs text-blue-500 hover:underline">Tout voir</a>
            </div>
            <table class="min-w-full text-sm">
                @forelse($derniersDevis as $d)
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-5 py-3"><a href="{{ route('devis.show', $d) }}" class="font-mono text-gray-900 hover:text-blue-600">{{ $d->numero }}</a></td>
                        <td class="px-5 py-3 text-gray-600">{{ $d->client->nom }}</td>
                        <td class="px-5 py-3"><x-badge :statut="$d->statut"/></td>
                        <td class="px-5 py-3 text-right font-medium">{{ number_format($d->montant_ttc, 0, ',', ' ') }} €</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400 text-sm">Aucun devis</td></tr>
                @endforelse
            </table>
        </div>

        {{-- Dernières factures --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-700">Dernières factures</h3>
                <a href="{{ route('factures.index') }}" class="text-xs text-blue-500 hover:underline">Tout voir</a>
            </div>
            <table class="min-w-full text-sm">
                @forelse($dernieresFactures as $f)
                    <tr class="border-b last:border-0 hover:bg-gray-50 {{ $f->estEnRetard() ? 'bg-red-50' : '' }}">
                        <td class="px-5 py-3"><a href="{{ route('factures.show', $f) }}" class="font-mono text-gray-900 hover:text-blue-600">{{ $f->numero }}</a></td>
                        <td class="px-5 py-3 text-gray-600">{{ $f->client->nom }}</td>
                        <td class="px-5 py-3"><x-badge :statut="$f->statut"/></td>
                        <td class="px-5 py-3 text-right font-medium {{ $f->estEnRetard() ? 'text-red-600' : '' }}">{{ number_format($f->montant_net_a_payer, 0, ',', ' ') }} €</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400 text-sm">Aucune facture</td></tr>
                @endforelse
            </table>
        </div>
    </div>
</x-app-layout>
