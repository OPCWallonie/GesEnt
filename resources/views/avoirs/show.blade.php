<x-app-layout>
    <x-slot name="header">
        @if($avoir->estBrouillon())
            <span class="text-gray-500">[Brouillon #{{ $avoir->id }}]</span>
        @else
            {{ $avoir->numero }}
        @endif
        <x-badge :statut="$avoir->statut"/>
    </x-slot>

    <x-slot name="actions">
        <x-barre-actions>
            <x-slot name="primaires">
                @if($avoir->estBrouillon())
                    <div x-data="{ confirmOpen: false }">
                        <button @click="confirmOpen = true"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Valider et émettre
                        </button>

                        <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                            <div @click.outside="confirmOpen = false" class="bg-white rounded-xl shadow-xl p-6 w-[480px] space-y-4">
                                <h3 class="font-semibold text-gray-800 text-lg">Émettre cet avoir ?</h3>
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm space-y-2">
                                    <p class="font-medium text-amber-800">Cette action est irréversible.</p>
                                    <ul class="text-amber-700 list-disc list-inside text-xs space-y-1">
                                        <li>Un numéro officiel AVO/{{ now()->year }}/XXXX sera alloué</li>
                                        <li>L'avoir passera en statut "Émis"</li>
                                        <li>Il ne pourra plus être modifié ni supprimé</li>
                                    </ul>
                                </div>
                                <form method="POST" action="{{ route('avoirs.emettre', $avoir) }}" class="flex gap-3 pt-1">
                                    @csrf
                                    <button type="button" @click="confirmOpen = false"
                                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                                        Annuler
                                    </button>
                                    <button type="submit"
                                            class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                                        Émettre
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <span class="inline-flex items-center gap-2 px-3 py-2 border border-gray-200 text-gray-400 text-sm rounded-lg cursor-not-allowed"
                          title="PDF disponible après émission">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </span>
                @else
                    <a href="{{ route('avoirs.pdf', $avoir) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </a>
                @endif
            </x-slot>

            <x-slot name="secondaires">
                @if(!$avoir->estBrouillon())
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
                @endif
                <a href="{{ route('factures.show', $avoir->facture_id) }}"
                   class="w-full inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                    ← Facture
                    @if($avoir->facture->estBrouillon())
                        [Brouillon #{{ $avoir->facture->id }}]
                    @else
                        {{ $avoir->facture->numero }}
                    @endif
                </a>
                @if($avoir->estBrouillon())
                    <form method="POST" action="{{ route('avoirs.destroy', $avoir) }}"
                          onsubmit="return confirm('Supprimer ce brouillon d\'avoir ? Cette action est irréversible.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full inline-flex items-center gap-2 px-3 py-2 border border-red-300 text-red-500 text-sm rounded-lg hover:bg-red-50">
                            Supprimer le brouillon
                        </button>
                    </form>
                @endif
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
                        <dd class="font-mono font-medium text-red-600">
                            @if($avoir->estBrouillon())
                                <span class="text-gray-500 italic">[Brouillon #{{ $avoir->id }}]</span>
                            @else
                                {{ $avoir->numero }}
                            @endif
                        </dd>
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
