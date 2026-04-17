<x-app-layout>
    <x-slot name="header">Changements de prix catalogue</x-slot>

    <x-slot name="actions">
        <a href="{{ route('catalog.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Retour au catalogue
        </a>
    </x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase">Total</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-red-100 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase">Hausses</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($stats['hausses']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-green-100 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase">Baisses</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($stats['baisses']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase">Variation moy.</p>
            @php $moy = round((float) $stats['variation_moy'], 2); @endphp
            <p class="text-2xl font-bold mt-1 {{ $moy > 0 ? 'text-red-600' : ($moy < 0 ? 'text-green-600' : 'text-gray-900') }}">
                {{ $moy > 0 ? '+' : '' }}{{ number_format($moy, 2, ',', '') }}%
            </p>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('catalog.changements-prix') }}" class="flex flex-wrap gap-3 mb-6 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Période</label>
            <div class="flex rounded-lg overflow-hidden border border-gray-300">
                @foreach(['7j' => '7 jours', '30j' => '30 jours', 'tout' => 'Tout'] as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['periode' => $val]) }}"
                   class="px-3 py-2 text-sm {{ $periode === $val ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Fournisseur</label>
            <select name="fournisseur" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Tous les fournisseurs</option>
                @foreach($fournisseurs as $f)
                    <option value="{{ $f }}" @selected($fournisseur === $f)>
                        {{ \App\Models\CatalogProduit::FOURNISSEURS[$f] ?? ucfirst($f) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">&nbsp;</label>
            <label class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="significatifs_uniquement" value="1"
                       @checked($significatifs) onchange="this.form.submit()"
                       class="rounded text-blue-600">
                Significatifs uniquement (≥ 3%)
            </label>
        </div>
        @if($fournisseur || $significatifs)
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">&nbsp;</label>
            <a href="{{ route('catalog.changements-prix', ['periode' => $periode]) }}"
               class="inline-flex items-center px-3 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">
                Effacer filtres
            </a>
        </div>
        @endif
    </form>

    {{-- Tableau --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($changements->isEmpty())
            <div class="p-12 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p class="font-medium">Aucun changement de prix sur cette période</p>
                <p class="text-sm mt-1">Les changements sont détectés lors des imports CSV ou des synchronisations API.</p>
            </div>
        @else
            @php
                $palette = ['bg-blue-100 text-blue-700','bg-purple-100 text-purple-700','bg-orange-100 text-orange-700','bg-green-100 text-green-700','bg-pink-100 text-pink-700','bg-cyan-100 text-cyan-700','bg-amber-100 text-amber-700','bg-indigo-100 text-indigo-700'];
            @endphp
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Référence / Désignation</th>
                        <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 uppercase">Avant</th>
                        <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 uppercase">Après</th>
                        <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 uppercase">Variation</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Source</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($changements as $c)
                    @php $idx = abs(crc32($c->fournisseur)) % count($palette); @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ $c->detected_at->format('d/m H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full {{ $palette[$idx] }}">
                                {{ \App\Models\CatalogProduit::FOURNISSEURS[$c->fournisseur] ?? ucfirst($c->fournisseur) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-gray-500">{{ $c->reference }}</span>
                            @if($c->catalogProduit)
                                <span class="block text-sm font-medium text-gray-800">{{ $c->catalogProduit->designation }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">
                            {{ number_format($c->prix_avant, 2, ',', ' ') }} €
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">
                            {{ number_format($c->prix_apres, 2, ',', ' ') }} €
                        </td>
                        <td class="px-4 py-3 text-right">
                            @php $pct = (float) $c->variation_pct; @endphp
                            <span class="font-bold {{ $pct > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $pct > 0 ? '+' : '' }}{{ number_format($pct, 2, ',', '') }}%
                            </span>
                            @if($c->est_significatif)
                                <span class="ml-1" title="Variation significative (≥ 3%)">⚠️</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full
                                {{ $c->source === 'csv' ? 'bg-gray-100 text-gray-600' : 'bg-indigo-100 text-indigo-700' }}">
                                {{ strtoupper($c->source) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $changements->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
