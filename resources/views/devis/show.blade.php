<x-app-layout>
    <x-slot name="header">Devis {{ $devis->numero }}</x-slot>

    <x-slot name="actions">
        <div class="flex items-center gap-2">
            @if((string) $devis->statut === 'valide' && !$devis->bonCommande)
                <form method="POST" action="{{ route('devis.convertir-bdc', $devis) }}">
                    @csrf
                    <button type="submit"
                            class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Convertir en BDC
                    </button>
                </form>
            @endif
            <a href="{{ route('devis.pdf', $devis) }}" target="_blank"
               class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                PDF
            </a>
            {{-- Dupliquer --}}
            <form method="POST" action="{{ route('devis.dupliquer', $devis) }}"
                  onsubmit="return confirm('Dupliquer ce devis en un nouveau brouillon ?')">
                @csrf
                <button type="submit"
                        class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Dupliquer
                </button>
            </form>
            {{-- Sauvegarder comme kit --}}
            <div x-data="{ open: false }">
                <button type="button" @click="open = true"
                        class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Sauvegarder comme kit
                </button>
                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div @click.outside="open = false" class="bg-white rounded-xl shadow-xl p-6 w-96 space-y-4">
                        <h3 class="font-semibold text-gray-800">Créer un kit depuis ce devis</h3>
                        <p class="text-sm text-gray-500">Les {{ $devis->lignes->count() }} lignes de ce devis seront sauvegardées comme modèle réutilisable.</p>
                        <form method="POST" action="{{ route('devis.sauvegarder-kit', $devis) }}">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du kit *</label>
                                    <input type="text" name="nom" required placeholder="Ex: Salle de bain standard"
                                           class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                                    <input type="text" name="categorie" placeholder="Ex: Sanitaire, Chauffage, Toiture…"
                                           class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea name="description" rows="2" placeholder="Courte description du contenu…"
                                              class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                                </div>
                                <div class="flex gap-3 pt-2">
                                    <button type="button" @click="open = false"
                                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                                        Annuler
                                    </button>
                                    <button type="submit"
                                            class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                        Créer le kit
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            {{-- Bouton Envoyer par email --}}
            <div x-data="{ open: false }">
                <button @click="open = true"
                        class="border border-indigo-300 text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Envoyer
                </button>
                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div @click.outside="open = false" class="bg-white rounded-xl shadow-xl p-6 w-[480px] space-y-4">
                        <h3 class="font-semibold text-gray-800">Envoyer le devis par email</h3>
                        <form method="POST" action="{{ route('devis.envoyer', $devis) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Destinataire *</label>
                                <input type="email" name="email" required
                                       value="{{ $devis->client->email ?? '' }}"
                                       class="w-full rounded-lg border-gray-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Message (optionnel)</label>
                                <textarea name="message" rows="5"
                                          class="w-full rounded-lg border-gray-300 text-sm">{{ $messageEmailDefaut ?? '' }}</textarea>
                            </div>
                            <p class="text-xs text-gray-400">Le PDF du devis sera joint automatiquement.</p>
                            <div class="flex gap-3 pt-1">
                                <button type="button" @click="open = false"
                                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Annuler</button>
                                <button type="submit"
                                        class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Envoyer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @if((string) $devis->statut !== 'archive')
                <a href="{{ route('devis.edit', $devis) }}"
                   class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
            @endif
            <form method="POST" action="{{ route('devis.destroy', $devis) }}"
                  onsubmit="return confirm('Supprimer définitivement ce devis ?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="border border-red-300 text-red-500 hover:bg-red-50 px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Supprimer
                </button>
            </form>
        </div>
    </x-slot>

    {{-- Bandeau alertes prix fournisseur --}}
    @if(!empty($lignesImpactees))
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <h3 class="font-semibold text-amber-900 mb-2">
                    {{ count($lignesImpactees) }} ligne(s) concernée(s) par un changement de prix fournisseur
                </h3>
                <div class="space-y-2 text-sm">
                    @foreach($lignesImpactees as $info)
                        <div class="flex items-center justify-between bg-white rounded px-3 py-2">
                            <span class="font-medium text-gray-700 truncate">{{ $info['ligne']->designation }}</span>
                            <span class="text-xs text-gray-500 whitespace-nowrap ml-3">
                                Prix devis : {{ number_format($info['prix_devis'], 2, ',', ' ') }} €
                                → Catalogue actuel : <strong>{{ number_format($info['prix_catalogue_actuel'], 2, ',', ' ') }} €</strong>
                                <span class="{{ $info['variation_pct'] > 0 ? 'text-red-600' : 'text-green-600' }} font-semibold">
                                    ({{ $info['variation_pct'] > 0 ? '+' : '' }}{{ number_format($info['variation_pct'], 1) }}%)
                                </span>
                            </span>
                        </div>
                    @endforeach
                </div>
                @if((string) $devis->statut !== 'archive')
                    <p class="text-xs text-amber-700 mt-3">
                        💡 Pensez à modifier le devis pour refléter les nouveaux prix avant conversion en BDC.
                    </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Colonne principale --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- En-tête --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ $devis->numero }}</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Émis le {{ $devis->date_document->format('d/m/Y') }}
                            @if($devis->date_validite)
                                · Valide jusqu'au
                                <span class="{{ $devis->date_validite->isPast() && (string) $devis->statut !== 'valide' ? 'text-red-600 font-medium' : '' }}">
                                    {{ $devis->date_validite->format('d/m/Y') }}
                                </span>
                            @endif
                        </p>
                    </div>
                    <x-badge :statut="$devis->statut"/>
                </div>

                @if($devis->bonCommande)
                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                        Converti en
                        <a href="{{ route('bons-commande.show', $devis->bonCommande) }}" class="font-semibold underline">
                            BDC {{ $devis->bonCommande->numero }}
                        </a>
                    </div>
                @endif
            </div>

            {{-- Infos client --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Client</h3>
                @if($devis->client)
                    <p class="font-medium text-gray-800">{{ $devis->client->nom }}</p>
                    @if($devis->client->adresse)
                        <p class="text-sm text-gray-500 mt-1">{{ $devis->client->adresse }}</p>
                    @endif
                    @if($devis->client->code_postal || $devis->client->ville)
                        <p class="text-sm text-gray-500">{{ $devis->client->code_postal }} {{ $devis->client->ville }}</p>
                    @endif
                    @if($devis->client->pays && $devis->client->pays !== 'Belgique')
                        <p class="text-sm text-gray-500">{{ $devis->client->pays }}</p>
                    @endif
                @endif
                @if($devis->chantier)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Chantier</p>
                        <p class="text-sm text-gray-700 mt-1">{{ $devis->chantier->nom }}</p>
                    </div>
                @endif
            </div>

            {{-- Lignes --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700">Lignes du devis</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Désignation</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qté</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unité</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">P.U. HT</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Remise</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">TVA</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant HT</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($devis->lignes as $ligne)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-gray-800">{{ $ligne->designation }}</p>
                                    @if($ligne->detail)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $ligne->detail }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($ligne->quantite, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $ligne->unite ?? '' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }} €</td>
                                <td class="px-4 py-3 text-right text-sm text-gray-500">
                                    {{ $ligne->remise ? number_format($ligne->remise, 2, ',', ' ') . ' %' : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $ligne->taux_tva }} %</td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-800">
                                    {{ number_format($ligne->montant_ht, 2, ',', ' ') }} €
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Notes --}}
            @if($devis->notes)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Notes</h3>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $devis->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Colonne latérale – Totaux --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Récapitulatif</h3>

                <dl class="space-y-2 text-sm">
                    @foreach($totauxTva as $taux => $montants)
                        <div class="flex justify-between text-gray-600">
                            <dt>Base HT {{ $taux }} %</dt>
                            <dd>{{ number_format($montants['ht'], 2, ',', ' ') }} €</dd>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <dt>TVA {{ $taux }} %</dt>
                            <dd>{{ number_format($montants['tva'], 2, ',', ' ') }} €</dd>
                        </div>
                    @endforeach

                    @if(($devis->frais_port ?? 0) > 0)
                        <div class="flex justify-between text-gray-600 pt-2 border-t border-gray-100">
                            <dt>Frais de port</dt>
                            <dd>{{ number_format($devis->frais_port, 2, ',', ' ') }} €</dd>
                        </div>
                    @endif

                    @if(($devis->ristourne_globale ?? 0) > 0)
                        <div class="flex justify-between text-gray-600">
                            <dt>Ristourne ({{ $devis->ristourne_globale }} %)</dt>
                            <dd class="text-red-600">- {{ number_format($devis->montant_ristourne ?? 0, 2, ',', ' ') }} €</dd>
                        </div>
                    @endif

                    <div class="flex justify-between font-bold text-gray-800 text-base pt-3 border-t border-gray-200">
                        <dt>Total TTC</dt>
                        <dd>{{ number_format($devis->montant_ttc ?? 0, 2, ',', ' ') }} €</dd>
                    </div>

                    @if(($devis->acompte ?? 0) > 0)
                        <div class="flex justify-between text-gray-600">
                            <dt>Acompte</dt>
                            <dd class="text-red-600">- {{ number_format($devis->acompte, 2, ',', ' ') }} €</dd>
                        </div>
                        <div class="flex justify-between font-semibold text-gray-800 pt-2 border-t border-gray-200">
                            <dt>Net à payer</dt>
                            <dd>{{ number_format(($devis->montant_ttc ?? 0) - ($devis->acompte ?? 0), 2, ',', ' ') }} €</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Infos paiement --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-2 text-sm">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Conditions</h3>
                @if($devis->modePaiement)
                    <div class="flex justify-between text-gray-600">
                        <span>Mode de paiement</span>
                        <span class="font-medium text-gray-700">{{ $devis->modePaiement->nom }}</span>
                    </div>
                @endif
                @if($devis->delai_reglement)
                    <div class="flex justify-between text-gray-600">
                        <span>Délai règlement</span>
                        <span class="font-medium text-gray-700">{{ $devis->delai_reglement }} jours</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Historique des envois email --}}
        @if($devis->emailEnvois->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-3 text-sm">Historique des envois email</h3>
            <div class="space-y-2">
                @foreach($devis->emailEnvois as $envoi)
                <div class="flex items-start gap-3 text-sm py-2 border-b border-gray-100 last:border-0">
                    <div class="mt-0.5">
                        @if($envoi->statut === 'envoye')
                            <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                        @else
                            <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-800">{{ $envoi->destinataire }}</p>
                        @if($envoi->statut === 'erreur')
                            <p class="text-xs text-red-600 mt-0.5">{{ $envoi->erreur }}</p>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs text-gray-400">{{ $envoi->envoye_at->format('d/m/Y H:i') }}</p>
                        @if($envoi->sender)
                            <p class="text-xs text-gray-400">{{ $envoi->sender->name }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
