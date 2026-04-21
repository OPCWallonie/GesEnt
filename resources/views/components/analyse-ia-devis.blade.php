@props(['analyse'])

@php
    $bgAlerte = match($analyse->niveauAlerte) {
        'important' => 'bg-red-50 border-red-200',
        'attention' => 'bg-amber-50 border-amber-200',
        default     => 'bg-blue-50 border-blue-200',
    };
    $iconAlerte = match($analyse->niveauAlerte) {
        'important' => '🚨',
        'attention' => '⚠️',
        default     => 'ℹ️',
    };
    $actionLabels = [
        'stocker'          => ['🧺', 'Stocker maintenant'],
        'reporter'         => ['⏸️', 'Reporter l\'achat'],
        'swap_fournisseur' => ['🔁', 'Changer de fournisseur'],
        'negocier'         => ['💬', 'Négocier avec le client'],
        'aucune'           => ['•',  'Sans action'],
    ];
@endphp

<div class="space-y-4">
    {{-- Synthèse --}}
    <div class="{{ $bgAlerte }} border rounded-lg p-4">
        <div class="flex items-start gap-3">
            <span class="text-2xl leading-none">{{ $iconAlerte }}</span>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">{{ $analyse->synthese }}</p>
                @if(count($analyse->recommandations) > 0 && $analyse->economieTotale() != 0)
                    <p class="text-xs text-gray-600 mt-2">
                        Économie potentielle estimée :
                        <strong class="{{ $analyse->economieTotale() >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ $analyse->economieTotale() >= 0 ? '+' : '' }}{{ number_format($analyse->economieTotale(), 2, ',', ' ') }} €
                        </strong>
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Recommandations détaillées --}}
    @if(count($analyse->recommandations) > 0)
        <div class="space-y-3">
            @foreach($analyse->recommandations as $reco)
                @php [$icone, $label] = $actionLabels[$reco->actionSuggeree] ?? ['•', 'Sans action']; @endphp
                <div class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 bg-white">
                    <span class="text-xl leading-none flex-shrink-0">{{ $icone }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('catalog.show', $reco->catalogProduitId) }}"
                               class="font-medium text-gray-900 hover:text-indigo-600">
                                {{ $reco->designation }}
                            </a>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ $label }}
                            </span>
                            @if(abs($reco->economieEstimeeEur) > 0.01)
                                <span class="text-xs {{ $reco->economieEstimeeEur >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $reco->economieEstimeeEur >= 0 ? '+' : '' }}{{ number_format($reco->economieEstimeeEur, 2, ',', ' ') }} €
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ $reco->justification }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-500 italic">Aucune recommandation spécifique — la synthèse ci-dessus couvre l'essentiel.</p>
    @endif
</div>
