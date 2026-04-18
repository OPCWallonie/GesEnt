<x-app-layout>
    <x-slot name="header">Nouvelle facture</x-slot>

    <form method="POST" action="{{ route('factures.store') }}" class="space-y-6">
        @csrf

        @if($bdcSource)
            <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Facturation depuis le BDC <strong>{{ $bdcSource->numero }}</strong> — Client : <strong>{{ $bdcSource->client->nom }}</strong>
                @if($bdcSource->avenants->count() > 0)
                    — inclut <strong>{{ $bdcSource->avenants->count() }}</strong> avenant(s)
                @endif
            </div>
            <input type="hidden" name="bon_commande_id" value="{{ $bdcSource->id }}">
        @endif

        {{-- Bloc situation (facturation partielle depuis un BDC) --}}
        @if($infoSituation)
        <div x-data="{ pct: {{ min(100, $infoSituation['pct_restant']) }} }"
             class="bg-indigo-50 border border-indigo-200 rounded-xl p-5">
            <h3 class="font-semibold text-indigo-900 mb-3">
                Situation n°{{ $infoSituation['numero_situation'] }}
                — BDC {{ $bdcSource->numero }}
            </h3>

            @if($infoSituation['factures_precedentes']->isNotEmpty())
            <div class="text-sm text-indigo-700 mb-3 space-y-1">
                @foreach($infoSituation['factures_precedentes'] as $fp)
                <div class="flex justify-between">
                    <span>Situation {{ $fp->numero_situation }} ({{ $fp->numero }})</span>
                    <span>{{ number_format($fp->pourcentage_avancement, 0) }}% — {{ number_format($fp->montant_ttc, 2, ',', ' ') }} €</span>
                </div>
                @endforeach
            </div>
            @endif

            <div class="grid grid-cols-3 gap-4 text-sm mb-4">
                <div>
                    <span class="text-indigo-500">Déjà facturé</span>
                    <div class="font-bold text-indigo-900">{{ number_format($infoSituation['pct_deja_facture'], 0) }}%</div>
                    <div class="text-xs text-indigo-500">{{ number_format($infoSituation['montant_deja_facture'], 2, ',', ' ') }} €</div>
                </div>
                <div>
                    <span class="text-indigo-500">Reste à facturer</span>
                    <div class="font-bold text-indigo-900">{{ number_format($infoSituation['pct_restant'], 0) }}%</div>
                    <div class="text-xs text-indigo-500">{{ number_format($infoSituation['montant_restant'], 2, ',', ' ') }} €</div>
                </div>
                <div>
                    <span class="text-indigo-500">Montant total BDC</span>
                    <div class="font-bold text-indigo-900">{{ number_format($infoSituation['montant_total_bdc'], 2, ',', ' ') }} €</div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-indigo-800 mb-1">
                    Avancement de cette situation : <span x-text="pct + '%'" class="font-bold"></span>
                </label>
                <input type="range" name="pourcentage_avancement" x-model="pct"
                       min="1" max="{{ $infoSituation['pct_restant'] }}" step="1"
                       class="w-full accent-indigo-600">
                <input type="hidden" name="numero_situation" value="{{ $infoSituation['numero_situation'] }}">
                <input type="hidden" name="pourcentage_cumule"
                       :value="{{ $infoSituation['pct_deja_facture'] }} + parseInt(pct)">
                <p class="text-xs text-indigo-500 mt-1">
                    Les lignes sont saisies librement ; ce pourcentage est enregistré pour le suivi d'avancement du BDC.
                </p>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 border-b pb-2">Informations</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de facture *</label>
                    <input type="date" name="date_document" value="{{ old('date_document', date('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date d'échéance</label>
                    <input type="date" name="date_echeance" value="{{ old('date_echeance') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="statut" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="en_attente">En attente</option>
                        <option value="envoyee">Envoyée</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mode de règlement</label>
                    <select name="mode_paiement_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">—</option>
                        @foreach($modesPaiement as $m)
                            <option value="{{ $m->id }}"
                                    @selected(old('mode_paiement_id', $bdcSource?->mode_paiement_id) == $m->id)>
                                {{ $m->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Délai règlement (jours)</label>
                    <input type="number" name="delai_reglement" value="{{ old('delai_reglement', $bdcSource?->delai_reglement ?? 30) }}" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frais de port (€ HT)</label>
                    <input type="number" name="frais_port" value="{{ old('frais_port', $totauxBdc ? $totauxBdc['frais_port'] : 0) }}" step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ristourne globale (%)</label>
                    <input type="number" name="ristourne_globale" value="{{ old('ristourne_globale', 0) }}" step="0.01" min="0" max="100"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Acompte à déduire (€)</label>
                    <input type="number" name="acompte_deduit" value="{{ old('acompte_deduit', $totauxBdc ? $totauxBdc['acompte'] : 0) }}" step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Retenue de garantie (%)
                        <span class="text-xs text-gray-400 font-normal">— loi belge, généralement 5%</span>
                    </label>
                    <input type="number" name="retenue_garantie_pct" value="{{ old('retenue_garantie_pct', 0) }}" step="0.01" min="0" max="100"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Lignes pré-remplies depuis BDC si applicable --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700">Lignes</h2>
                @if($bdcSource && $totauxBdc)
                    <p class="text-xs text-gray-400 mt-1">
                        Total BDC + avenants : <strong>{{ number_format($totauxBdc['ttc'], 2, ',', ' ') }} € TTC</strong>
                    </p>
                @endif
            </div>
            @php
                $lignesInitiales = $bdcSource ? $bdcSource->toutesLesLignes() : collect();
            @endphp
            <x-lignes-document :lignes-initiales="$lignesInitiales" :taux-tva="$tauxTva" :tva-defaut="21" :client-id="$bdcSource->client_id ?? null"/>
        </div>

        <div class="flex justify-between">
            <a href="{{ $bdcSource ? route('bons-commande.show', $bdcSource) : route('factures.index') }}"
               class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">← Retour</a>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                Créer la facture
            </button>
        </div>
    </form>
</x-app-layout>
