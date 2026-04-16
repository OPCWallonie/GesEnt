<x-app-layout>
    <x-slot name="header">{{ $facture->numero }}
        @if($facture->statut === 'payee')
            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Payée</span>
        @elseif($facture->estEnRetard())
            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">En retard</span>
        @else
            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">En attente</span>
        @endif
    </x-slot>
    <x-slot name="actions">
        @if($facture->statut !== 'payee')
            <button x-data @click="$dispatch('open-modal', 'marquer-payee')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                ✓ Marquer payée
            </button>
            <a href="{{ route('factures-achat.edit', $facture) }}"
               class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                Modifier
            </a>
        @endif
    </x-slot>

    {{-- Encadré Peppol --}}
    @if($facture->peppol_source === 'peppol')
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 text-sm text-indigo-800">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span class="font-semibold">Reçue automatiquement via Peppol</span>
            <span class="text-indigo-500 text-xs">{{ $facture->peppol_recu_at?->format('d/m/Y à H:i') }}</span>
        </div>
        <p class="text-xs text-indigo-600 mt-1">
            Expéditeur Peppol : {{ $facture->peppol_sender_id ?? '—' }}
            · Vérifiez la catégorie et le chantier lié avant de valider.
        </p>
    </div>
    @endif

    {{-- Modal marquer payée --}}
    <div x-data="{ open: false }"
         @open-modal.window="if ($event.detail === 'marquer-payee') open = true"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div @click.outside="open = false" class="bg-white rounded-xl shadow-xl p-6 w-80 space-y-4">
            <h3 class="font-semibold text-gray-800">Enregistrer le paiement</h3>
            <form method="POST" action="{{ route('factures-achat.marquer-payee', $facture) }}">
                @csrf @method('PATCH')
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de paiement *</label>
                        <input type="date" name="date_paiement" value="{{ date('Y-m-d') }}" required
                               class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="open = false"
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg">Annuler</button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Confirmer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-3 lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Informations</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-400">Numéro</dt><dd class="font-mono font-medium">{{ $facture->numero }}</dd></div>
                    @if($facture->reference_fournisseur)
                        <div class="flex justify-between"><dt class="text-gray-400">Réf. fournisseur</dt><dd class="font-mono text-xs">{{ $facture->reference_fournisseur }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-gray-400">Date</dt><dd>{{ $facture->date_document->format('d/m/Y') }}</dd></div>
                    @if($facture->date_echeance)
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Échéance</dt>
                            <dd class="{{ $facture->estEnRetard() ? 'text-red-600 font-medium' : '' }}">
                                {{ $facture->date_echeance->format('d/m/Y') }}
                            </dd>
                        </div>
                    @endif
                    <div class="flex justify-between"><dt class="text-gray-400">Catégorie</dt><dd>{{ $facture->label_categorie }}</dd></div>
                    @if($facture->date_paiement)
                        <div class="flex justify-between"><dt class="text-gray-400">Payée le</dt><dd class="text-green-600 font-medium">{{ $facture->date_paiement->format('d/m/Y') }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Fournisseur</h3>
                <a href="{{ route('fournisseurs.show', $facture->fournisseur) }}" class="font-medium text-blue-600 hover:underline">{{ $facture->fournisseur->nom }}</a>
                @if($facture->fournisseur->ville)
                    <div class="text-sm text-gray-500 mt-1">{{ $facture->fournisseur->ville }}</div>
                @endif
            </div>

            @if($facture->chantier || $facture->bonCommande)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-700 mb-3 text-sm">Lié à</h3>
                    @if($facture->chantier)
                        <div class="text-sm mb-1">
                            <span class="text-gray-400 text-xs">Chantier</span><br>
                            <a href="{{ route('chantiers.show', $facture->chantier) }}" class="text-blue-600 hover:underline">{{ $facture->chantier->nom }}</a>
                        </div>
                    @endif
                    @if($facture->bonCommande)
                        <div class="text-sm">
                            <span class="text-gray-400 text-xs">BDC</span><br>
                            <a href="{{ route('bons-commande.show', $facture->bonCommande) }}" class="font-mono text-blue-600 hover:underline">{{ $facture->bonCommande->numero }}</a>
                        </div>
                    @endif
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Montants</h3>
                <dl class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-600"><dt>HT</dt><dd>{{ number_format($facture->montant_ht, 2, ',', ' ') }} €</dd></div>
                    <div class="flex justify-between text-gray-400 text-xs"><dt>TVA {{ number_format($facture->taux_tva, 0) }}%</dt><dd>{{ number_format($facture->montant_tva, 2, ',', ' ') }} €</dd></div>
                    <div class="flex justify-between font-bold text-gray-900 border-t border-gray-200 pt-2 mt-2 text-base">
                        <dt>Total TTC</dt><dd>{{ number_format($facture->montant_ttc, 2, ',', ' ') }} €</dd>
                    </div>
                </dl>
            </div>

            @if($facture->statut !== 'payee')
                <form method="POST" action="{{ route('factures-achat.destroy', $facture) }}"
                      onsubmit="return confirm('Supprimer cette facture ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 text-sm text-red-500 hover:text-red-700 border border-red-200 rounded-lg hover:bg-red-50">
                        Supprimer
                    </button>
                </form>
            @endif
        </div>

        <div class="col-span-3 lg:col-span-2 space-y-4">
            @if($facture->notes)
                <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-5">
                    <h3 class="text-sm font-semibold text-yellow-800 mb-2">Notes</h3>
                    <p class="text-sm text-yellow-900 whitespace-pre-wrap">{{ $facture->notes }}</p>
                </div>
            @endif

            @if($facture->has_fichier)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                        <div class="flex items-center gap-2 text-sm font-medium text-gray-700">
                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
                            </svg>
                            {{ $facture->fichier_nom_original ?? 'Document original' }}
                        </div>
                        <a href="{{ $facture->fichier_url }}" target="_blank"
                           class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Ouvrir dans un nouvel onglet
                        </a>
                    </div>
                    @if(str_starts_with($facture->fichier_mime ?? '', 'image/'))
                        <img src="{{ $facture->fichier_url }}" alt="Facture" class="w-full object-contain max-h-[80vh]">
                    @else
                        <iframe src="{{ $facture->fichier_url }}"
                                class="w-full"
                                style="height: 80vh; min-height: 500px;"
                                title="Facture originale">
                        </iframe>
                    @endif
                </div>
            @elseif(!$facture->notes)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-400 text-sm">
                    Aucune note ni document pour cette facture.
                    @if($facture->statut !== 'payee')
                        <br><a href="{{ route('factures-achat.edit', $facture) }}" class="text-blue-500 hover:underline mt-2 block">Modifier pour ajouter des informations</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
