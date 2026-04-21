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

        {{-- Badge volatilité détaillé --}}
        @hasanyrole('admin|comptable')
        @if($volatiliteBadge->visible())
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Analyse de volatilité</h2>
            <x-volatilite-badge :badge="$volatiliteBadge" variant="detaille"/>

            @if($catalogProduit->volatilite_calculee_at)
            <div class="mt-4 flex flex-wrap gap-3 text-xs text-gray-500">
                @if($catalogProduit->volatilite_tendance_pct !== null)
                    <span>Tendance 12m :
                        <strong class="{{ (float)$catalogProduit->volatilite_tendance_pct >= 0 ? 'text-orange-600' : 'text-green-600' }}">
                            {{ (float)$catalogProduit->volatilite_tendance_pct > 0 ? '+' : '' }}{{ number_format($catalogProduit->volatilite_tendance_pct, 1) }}%
                        </strong>
                    </span>
                @endif
                @if($catalogProduit->volatilite_amplitude_pct !== null)
                    <span>Amplitude : <strong>{{ number_format($catalogProduit->volatilite_amplitude_pct, 1) }}%</strong></span>
                @endif
                @if($catalogProduit->volatilite_nb_changements)
                    <span>{{ $catalogProduit->volatilite_nb_changements }} changements</span>
                @endif
                @if($catalogProduit->volatilite_calculee_at)
                    <span>Calculé le {{ $catalogProduit->volatilite_calculee_at->format('d/m/Y') }}</span>
                @endif
            </div>

            @php
                // Sparkline SVG inline (prix depuis historique)
                $sparkPoints = $historique->sortBy('detected_at')->values();
            @endphp
            @if($sparkPoints->count() >= 2)
            @php
                $prix  = $sparkPoints->pluck('prix_apres')->map(fn($v) => (float)$v);
                $mn    = $prix->min();
                $mx    = $prix->max();
                $range = $mx - $mn ?: 1;
                $w     = 200;
                $h     = 40;
                $n     = $prix->count();
                $pts   = $prix->values()->map(function($p, $i) use ($n, $w, $h, $mn, $range) {
                    $x = $n > 1 ? round($i / ($n - 1) * $w, 1) : $w / 2;
                    $y = round($h - (($p - $mn) / $range) * ($h - 4) - 2, 1);
                    return "{$x},{$y}";
                })->implode(' ');
            @endphp
            <div class="mt-3">
                <p class="text-xs text-gray-400 mb-1">Évolution récente</p>
                <svg viewBox="0 0 {{ $w }} {{ $h }}" width="{{ $w }}" height="{{ $h }}" class="block">
                    <polyline points="{{ $pts }}" fill="none" stroke="#f59e0b" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round"/>
                </svg>
            </div>
            @endif
            @endif
        </div>
        @endif

        {{-- Alternatives cross-fournisseurs --}}
        @if($alternatives->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">
                Alternatives fournisseurs (même EAN)
                <span class="text-xs font-normal text-gray-400">— classées par opportunité</span>
            </h2>
            <div class="space-y-3">
                @foreach($alternatives as $alt)
                @php
                    $p = $alt->produit;
                @endphp
                <div class="flex items-start justify-between gap-3 p-3 rounded-lg border {{ $alt->nbSignaux() > 0 ? 'border-green-200 bg-green-50' : 'border-gray-100 bg-gray-50' }}">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                {{ $p->nom_fournisseur ?? $p->fournisseur }}
                            </span>
                            <span class="font-mono text-xs text-gray-500">{{ $p->reference }}</span>
                            @unless($p->en_stock)
                                <span class="text-xs text-red-500">Rupture</span>
                            @endunless
                        </div>
                        <div class="text-sm text-gray-700 mt-1 truncate">{{ $p->designation }}</div>
                        <div class="flex gap-2 mt-1.5 flex-wrap">
                            @if($alt->signalPrixInferieur)
                                <span class="text-xs text-green-700 bg-green-100 px-1.5 py-0.5 rounded">
                                    💰 Prix inférieur ({{ number_format($alt->ecartPrixPct, 1) }}%)
                                </span>
                            @endif
                            @if($alt->signalTendanceFavorable)
                                <span class="text-xs text-green-700 bg-green-100 px-1.5 py-0.5 rounded">📉 Tendance favorable</span>
                            @endif
                            @if($alt->signalPositionInferieure)
                                <span class="text-xs text-green-700 bg-green-100 px-1.5 py-0.5 rounded">📊 Position basse</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="font-semibold {{ $alt->nbSignaux() > 0 ? 'text-green-700' : 'text-gray-800' }}">
                            {{ number_format($p->prix_catalogue, 2, ',', ' ') }} €
                        </div>
                        @if($alt->ecartPrixPct != 0)
                            <div class="text-xs {{ $alt->ecartPrixPct < 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $alt->ecartPrixPct > 0 ? '+' : '' }}{{ number_format($alt->ecartPrixPct, 1) }}%
                            </div>
                        @endif
                        <a href="{{ route('catalog.show', $p) }}"
                           class="mt-1 inline-block text-xs px-2 py-1 border border-gray-300 rounded hover:bg-white">Voir</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Comportement volatilité --}}
        @hasanyrole('admin|comptable')
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Comportement volatilité</h2>

            @if($catalogProduit->volatilite_calculee_at && (!$volatiliteBadge->visible()))
                <div class="flex flex-wrap gap-3 mb-4 text-xs text-gray-600">
                    <span class="px-2 py-0.5 rounded-full
                        {{ match($catalogProduit->volatilite_classe) {
                            'stable'     => 'bg-green-100 text-green-700',
                            'a'          => 'bg-yellow-100 text-yellow-700',
                            'b'          => 'bg-orange-100 text-orange-700',
                            'c'          => 'bg-red-100 text-red-700',
                            'insuffisant'=> 'bg-gray-100 text-gray-500',
                            default      => 'bg-gray-100 text-gray-400',
                        } }}">
                        Classe : {{ strtoupper($catalogProduit->volatilite_classe ?? '—') }}
                    </span>
                    @if($catalogProduit->volatilite_tendance_pct !== null)
                        <span>Tendance 12m : {{ number_format($catalogProduit->volatilite_tendance_pct, 1) }}%</span>
                    @endif
                    @if($catalogProduit->volatilite_amplitude_pct !== null)
                        <span>Amplitude : {{ number_format($catalogProduit->volatilite_amplitude_pct, 1) }}%</span>
                    @endif
                    <span>Calculé : {{ $catalogProduit->volatilite_calculee_at->format('d/m/Y H:i') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('catalog.volatilite-flag', $catalogProduit) }}" class="flex items-end gap-3">
                @csrf @method('PATCH')
                <div class="flex-1 max-w-xs">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Override manuel</label>
                    <select name="volatilite_flag_manuel"
                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="auto" @selected($catalogProduit->volatilite_flag_manuel === 'auto')>Automatique (selon calculs)</option>
                        <option value="toujours_alerter" @selected($catalogProduit->volatilite_flag_manuel === 'toujours_alerter')>Toujours alerter</option>
                        <option value="jamais_alerter" @selected($catalogProduit->volatilite_flag_manuel === 'jamais_alerter')>Jamais alerter</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Force le comportement d'alerte indépendamment des calculs automatiques.</p>
                </div>
                <button type="submit" class="px-3 py-2 bg-gray-700 text-white text-sm rounded-lg hover:bg-gray-800">
                    Appliquer
                </button>
            </form>
        </div>
        @endhasanyrole

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
