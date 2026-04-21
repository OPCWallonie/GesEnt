<x-app-layout>
    <x-slot name="header">{{ $avoir->numero }}</x-slot>

    <x-slot name="actions">
        <x-barre-actions>
            <x-slot name="primaires">
                <a href="{{ route('avoirs.pdf', $avoir) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    PDF
                </a>
            </x-slot>

            <x-slot name="secondaires">
                @if($peppolMode !== 'desactive' && \App\Models\ParametresEntreprise::instance()->peppolActif())
                    @if(!$avoir->peppol_envoye_at)
                        <form method="POST" action="{{ route('avoirs.envoyer-peppol', $avoir) }}"
                              onsubmit="return confirm('Envoyer cet avoir via Peppol ?')">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Envoyer via Peppol
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg">
                            Peppol envoyé {{ $avoir->peppol_envoye_at->format('d/m/Y') }}
                        </span>
                    @endif
                @endif
                <a href="{{ route('factures.show', $avoir->facture_id) }}"
                   class="w-full inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                    ← Facture {{ $avoir->facture->numero }}
                </a>
                <form method="POST" action="{{ route('avoirs.destroy', $avoir) }}"
                      onsubmit="return confirm('Supprimer définitivement cet avoir ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full inline-flex items-center gap-2 px-3 py-2 border border-red-300 text-red-500 text-sm rounded-lg hover:bg-red-50">
                        Supprimer
                    </button>
                </form>
            </x-slot>
        </x-barre-actions>
    </x-slot>

    <div class="max-w-3xl grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Info card --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Avoir</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Numéro</dt>
                        <dd class="font-mono font-medium text-red-600">{{ $avoir->numero }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Date</dt>
                        <dd>{{ $avoir->date_document->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Client</dt>
                        <dd class="font-medium">{{ $avoir->client->nom }}</dd>
                    </div>
                    @if($avoir->chantier)
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Chantier</dt>
                        <dd>{{ $avoir->chantier->nom }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Facture liée</dt>
                        <dd>
                            <a href="{{ route('factures.show', $avoir->facture_id) }}"
                               class="text-blue-600 hover:underline font-mono">{{ $avoir->facture->numero }}</a>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-xl p-5">
                <h3 class="font-semibold text-red-700 mb-3 text-sm">Montants</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-red-600">HT</dt>
                        <dd>{{ number_format($avoir->montant_ht, 2, ',', ' ') }} €</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-red-600">TVA ({{ number_format($avoir->taux_tva, 0) }}%)</dt>
                        <dd>{{ number_format($avoir->montant_tva, 2, ',', ' ') }} €</dd>
                    </div>
                    <div class="flex justify-between border-t border-red-200 pt-2 mt-2">
                        <dt class="font-semibold text-red-800">TTC</dt>
                        <dd class="font-bold text-xl text-red-700">{{ number_format($avoir->montant_ttc, 2, ',', ' ') }} €</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Main content --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Note de crédit / Avoir</h3>

                <div class="mb-4">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Motif</p>
                    <p class="text-gray-800 whitespace-pre-line bg-gray-50 rounded-lg p-4 text-sm">{{ $avoir->motif }}</p>
                </div>

                @if($avoir->notes)
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Notes internes</p>
                        <p class="text-gray-600 text-sm whitespace-pre-line">{{ $avoir->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Alert crédit --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                <div class="font-semibold mb-1">Note comptable</div>
                Cet avoir de <strong>{{ number_format($avoir->montant_ttc, 2, ',', ' ') }} € TTC</strong>
                vient en déduction de la facture <strong>{{ $avoir->facture->numero }}</strong>
                ({{ number_format($avoir->facture->montant_net_a_payer, 2, ',', ' ') }} € TTC).
                Solde restant dû estimé :
                <strong>{{ number_format(max(0, $avoir->facture->montant_net_a_payer - $avoir->montant_ttc), 2, ',', ' ') }} €</strong>.
            </div>
        </div>
    </div>
</x-app-layout>
