<x-app-layout>
    <x-slot name="header">{{ $catalogProduit->designation }}</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- En-tête produit --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                            {{ $catalogProduit->nom_fournisseur ?? $catalogProduit->fournisseur }}
                        </span>
                        <span class="font-mono text-sm text-gray-500">{{ $catalogProduit->reference }}</span>
                        @if($catalogProduit->marque)
                            <span class="text-sm text-gray-600">· {{ $catalogProduit->marque }}</span>
                        @endif
                    </div>
                    <h1 class="text-xl font-semibold text-gray-900">{{ $catalogProduit->designation }}</h1>
                    @if($catalogProduit->ean)
                        <p class="text-xs text-gray-400 mt-1 font-mono">EAN : {{ $catalogProduit->ean }}</p>
                    @endif
                    @if($catalogProduit->description)
                        <p class="text-sm text-gray-600 mt-3">{{ $catalogProduit->description }}</p>
                    @endif
                </div>
                <div class="text-right flex-shrink-0">
                    <div class="text-xs text-gray-500">Prix catalogue</div>
                    <div class="text-2xl font-bold text-gray-900">
                        {{ number_format($catalogProduit->prix_catalogue, 2, ',', ' ') }} €
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        TVA {{ $catalogProduit->taux_tva }}% / {{ $catalogProduit->unite }}
                    </div>
                    @if(abs((float)$catalogProduit->prix_revente - (float)$catalogProduit->prix_catalogue) > 0.001)
                        <div class="text-sm text-indigo-600 mt-2">
                            Vente suggérée : {{ number_format($catalogProduit->prix_revente, 2, ',', ' ') }} €
                        </div>
                    @endif
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('catalog.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Retour au catalogue</a>
            </div>
        </div>

        {{-- Équivalents chez d'autres fournisseurs --}}
        @if($equivalents->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700">
                    Équivalents chez d'autres fournisseurs
                    <span class="text-xs font-normal text-gray-400 ml-1">
                        ({{ $equivalents->count() }} {{ $equivalents->count() > 1 ? 'alternatives' : 'alternative' }} · EAN identique)
                    </span>
                </h2>
            </div>

            @php
                $prixActuel = (float) $catalogProduit->prix_catalogue;
                $prixMin    = (float) $equivalents->min('prix_catalogue');
                $palette    = ['bg-blue-100 text-blue-700','bg-purple-100 text-purple-700','bg-orange-100 text-orange-700','bg-green-100 text-green-700','bg-pink-100 text-pink-700','bg-cyan-100 text-cyan-700'];
            @endphp

            <div class="space-y-2">
                @foreach($equivalents as $alt)
                    @php
                        $prixAlt = (float) $alt->prix_catalogue;
                        $ecart   = $prixActuel > 0 ? round(($prixAlt - $prixActuel) / $prixActuel * 100, 1) : 0;
                        $estLeMoinsCher = abs($prixAlt - $prixMin) < 0.001;
                    @endphp
                    <div class="flex items-center justify-between gap-3 p-3 rounded-lg border {{ $estLeMoinsCher ? 'border-green-200 bg-green-50' : 'border-gray-100 bg-gray-50' }}">
                        <div class="flex items-center gap-3 flex-1 min-w-0 flex-wrap">
                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full {{ $palette[abs(crc32($alt->fournisseur)) % count($palette)] }}">
                                {{ $alt->nom_fournisseur ?? $alt->fournisseur }}
                            </span>
                            <span class="font-mono text-xs text-gray-500">{{ $alt->reference }}</span>
                            <span class="text-sm text-gray-700 truncate">{{ $alt->designation }}</span>
                            @if($estLeMoinsCher)
                                <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full bg-green-200 text-green-800">
                                    ⭐ Meilleur prix
                                </span>
                            @endif
                            @unless($alt->en_stock)
                                <span class="text-xs text-red-500">Rupture</span>
                            @endunless
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="font-semibold {{ $estLeMoinsCher ? 'text-green-700' : 'text-gray-900' }}">
                                {{ number_format($prixAlt, 2, ',', ' ') }} €
                            </div>
                            @if($ecart != 0.0)
                                <div class="text-xs {{ $ecart < 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $ecart > 0 ? '+' : '' }}{{ number_format($ecart, 1) }}% vs ce fourn.
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('catalog.show', $alt) }}"
                           class="text-xs px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-white whitespace-nowrap">
                            Voir
                        </a>
                    </div>
                @endforeach
            </div>

            @if(abs($prixActuel - $prixMin) > 0.01)
                @php $economie = $prixActuel - $prixMin; @endphp
                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                    💡 Économie potentielle jusqu'à <strong>{{ number_format($economie, 2, ',', ' ') }} € par unité</strong>
                    en choisissant le fournisseur le moins cher ({{ number_format($economie / $prixActuel * 100, 1) }}% d'écart).
                </div>
            @endif
        </div>
        @elseif($catalogProduit->ean)
        <div class="bg-white rounded-xl border border-gray-200 p-6 text-sm text-gray-500">
            Aucun autre fournisseur ne propose ce produit (EAN {{ $catalogProduit->ean }}) actuellement.
        </div>
        @else
        <div class="bg-white rounded-xl border border-gray-200 p-6 text-sm text-gray-500">
            Ce produit n'a pas d'EAN renseigné — impossible de trouver des équivalents.
        </div>
        @endif

        {{-- Historique des prix --}}
        @if($historique->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Historique des changements de prix</h2>
            <div class="divide-y divide-gray-100">
                @foreach($historique as $h)
                    <div class="flex items-center justify-between gap-3 py-2 text-sm">
                        <span class="text-xs text-gray-500">{{ $h->detected_at->format('d/m/Y H:i') }}</span>
                        <div class="text-gray-700">
                            {{ number_format($h->prix_avant, 2, ',', ' ') }} €
                            <span class="text-gray-400 mx-1">→</span>
                            {{ number_format($h->prix_apres, 2, ',', ' ') }} €
                        </div>
                        <span class="font-semibold {{ $h->variation_pct > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $h->variation_pct > 0 ? '+' : '' }}{{ number_format($h->variation_pct, 1) }}%
                        </span>
                        @if($h->est_significatif)
                            <span class="text-xs" title="Variation significative (> 3%)">⚠️</span>
                        @endif
                        <span class="text-xs text-gray-400 uppercase">{{ $h->source }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
