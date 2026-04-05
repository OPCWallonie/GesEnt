<x-app-layout>
    <x-slot name="header">{{ $facture->numero }} <x-badge :statut="$facture->statut"/></x-slot>
    <x-slot name="actions">
        <a href="{{ route('factures.pdf', $facture) }}" target="_blank"
           class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            PDF
        </a>
        {{-- Envoyer par email --}}
        <div x-data="{ open: false }">
            <button @click="open = true"
                    class="inline-flex items-center gap-2 px-3 py-2 border border-indigo-300 text-indigo-600 text-sm rounded-lg hover:bg-indigo-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Envoyer
            </button>
            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                <div @click.outside="open = false" class="bg-white rounded-xl shadow-xl p-6 w-[480px] space-y-4">
                    <h3 class="font-semibold text-gray-800">Envoyer la facture par email</h3>
                    @if(($peppolMode ?? 'desactive') === 'desactive')
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <p class="text-xs text-amber-800 font-semibold">Attention — copie de courtoisie uniquement</p>
                        <p class="text-xs text-amber-700 mt-1">
                            Ce PDF est envoyé à titre informatif. Pour être conforme à la loi belge,
                            cette facture doit aussi être transmise via Peppol (directement ou via votre logiciel comptable).
                        </p>
                    </div>
                    @endif
                    <form method="POST" action="{{ route('factures.envoyer', $facture) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Destinataire *</label>
                            <input type="email" name="email" required
                                   value="{{ $facture->client->email ?? '' }}"
                                   class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message (optionnel)</label>
                            <textarea name="message" rows="4" class="w-full rounded-lg border-gray-300 text-sm"
                                      placeholder="Bonjour, veuillez trouver ci-joint notre facture…"></textarea>
                        </div>
                        <p class="text-xs text-gray-400">Le PDF de la facture sera joint automatiquement.</p>
                        <div class="flex gap-3 pt-1">
                            <button type="button" @click="open = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Annuler</button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                                @if(($peppolMode ?? 'desactive') === 'desactive')
                                    Envoyer copie PDF (courtoisie)
                                @elseif(($peppolMode ?? '') === 'envoi')
                                    Envoyer via Peppol + copie PDF
                                @else
                                    Envoyer PDF
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- Bouton Peppol --}}
        @if(($peppolMode ?? 'desactive') !== 'desactive' && \App\Models\ParametresEntreprise::instance()->peppolActif())
            @if(!$facture->peppol_envoye_at)
                <form method="POST" action="{{ route('factures.envoyer-peppol', $facture) }}"
                      onsubmit="return confirm('Envoyer cette facture via Peppol à {{ addslashes($facture->client->nom) }} ?')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Envoyer via Peppol
                    </button>
                </form>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Peppol {{ $facture->peppol_envoye_at->format('d/m/Y H:i') }}
                    @if($facture->peppol_reference)
                        <span class="text-xs text-green-500">({{ $facture->peppol_reference }})</span>
                    @endif
                </span>
            @endif
        @endif
        {{-- Avoir --}}
        @if((string) $facture->statut !== 'archive')
            <a href="{{ route('avoirs.create', $facture) }}"
               class="inline-flex items-center gap-2 px-3 py-2 border border-red-300 text-red-600 text-sm rounded-lg hover:bg-red-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                Avoir
            </a>
        @endif
        @if((string) $facture->statut !== 'payee')
            <a href="{{ route('factures.edit', $facture) }}"
               class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                Modifier
            </a>
        @endif
        @if(!in_array((string) $facture->statut, ['payee', 'archive']))
            @if($facture->estEnRetard() || in_array((string) $facture->statut, ['en_attente', 'envoyee']))
                <form method="POST" action="{{ route('factures.relancer', $facture) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-3 py-2 border border-amber-300 text-amber-700 text-sm rounded-lg hover:bg-amber-50"
                            title="{{ $facture->nb_relances > 0 ? 'Relance n°'.($facture->nb_relances+1).' — dernière le '.$facture->derniere_relance_at?->format('d/m/Y') : 'Enregistrer une relance' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        Relancer @if($facture->nb_relances > 0)<span class="font-bold">({{ $facture->nb_relances }})</span>@endif
                    </button>
                </form>
            @endif
            <button x-data @click="$dispatch('open-modal', 'marquer-payee')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                ✓ {{ $facture->paiements->isNotEmpty() ? 'Ajouter un paiement' : 'Marquer payée' }}
            </button>
        @endif
    </x-slot>

    {{-- Modal marquer payée --}}
    <div x-data="{ open: false }"
         @open-modal.window="if ($event.detail === 'marquer-payee') open = true"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div @click.outside="open = false" class="bg-white rounded-xl shadow-xl p-6 w-96 space-y-4">
            <h3 class="font-semibold text-gray-800">Enregistrer le paiement</h3>
            <form method="POST" action="{{ route('factures.marquer-payee', $facture) }}">
                @csrf @method('PATCH')
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de paiement *</label>
                        <input type="date" name="date_paiement" value="{{ date('Y-m-d') }}" required
                               class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant encaissé (€) *</label>
                        <input type="number" name="montant_paye"
                               value="{{ $facture->montant_restant > 0 ? $facture->montant_restant : $facture->montant_net_a_payer }}"
                               step="0.01" min="0.01" required
                               class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement</label>
                        <select name="mode" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">—</option>
                            <option value="virement">Virement</option>
                            <option value="cheque">Chèque</option>
                            <option value="cash">Cash</option>
                            <option value="carte">Carte bancaire</option>
                            <option value="domiciliation">Domiciliation</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Référence (n° chèque, réf virement…)</label>
                        <input type="text" name="reference" class="w-full rounded-lg border-gray-300 text-sm"
                               placeholder="Optionnel">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="open = false"
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                            Confirmer
                        </button>
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
                    <div class="flex justify-between"><dt class="text-gray-400">Date</dt><dd>{{ $facture->date_document->format('d/m/Y') }}</dd></div>
                    @if($facture->date_echeance)
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Échéance</dt>
                            <dd class="{{ $facture->estEnRetard() ? 'text-red-600 font-medium' : '' }}">
                                {{ $facture->date_echeance->format('d/m/Y') }}
                                @if($facture->estEnRetard()) <span class="text-xs">(en retard)</span> @endif
                            </dd>
                        </div>
                    @endif
                    <div class="flex justify-between"><dt class="text-gray-400">Statut</dt><dd><x-badge :statut="$facture->statut"/></dd></div>
                    @if($facture->modePaiement)
                        <div class="flex justify-between"><dt class="text-gray-400">Règlement</dt><dd>{{ $facture->modePaiement->nom }}</dd></div>
                    @endif
                    @if($facture->bonCommande)
                        <div class="flex justify-between">
                            <dt class="text-gray-400">BDC</dt>
                            <dd><a href="{{ route('bons-commande.show', $facture->bonCommande) }}" class="text-blue-600 hover:underline font-mono text-xs">{{ $facture->bonCommande->numero }}</a></dd>
                        </div>
                    @endif
                    @if($facture->nb_relances > 0)
                        <div class="flex justify-between"><dt class="text-gray-400">Relances</dt>
                            <dd class="text-amber-600 font-medium">{{ $facture->nb_relances }}
                                @if($facture->derniere_relance_at)
                                    <span class="text-xs text-gray-400">({{ $facture->derniere_relance_at->format('d/m/Y') }})</span>
                                @endif
                            </dd>
                        </div>
                    @endif
                    @if(!in_array((string) $facture->statut, ['payee', 'archive']))
                        <div class="flex justify-between items-center pt-1">
                            <dt class="text-gray-400 text-xs">Relance auto</dt>
                            <dd>
                                <form method="POST" action="{{ route('factures.toggle-relance-auto', $facture) }}">
                                    @csrf @method('PATCH')
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox" name="relance_auto" value="1"
                                               {{ $facture->relance_auto ? 'checked' : '' }}
                                               onchange="this.form.submit()"
                                               class="rounded border-gray-300 text-blue-600 text-xs">
                                        <span class="text-xs {{ $facture->relance_auto ? 'text-blue-600' : 'text-gray-400' }}">
                                            {{ $facture->relance_auto ? 'Activée' : 'Désactivée' }}
                                        </span>
                                    </label>
                                </form>
                            </dd>
                        </div>
                        @if($facture->prochaine_relance_at)
                            <div class="flex justify-between">
                                <dt class="text-gray-400 text-xs">Prochaine relance</dt>
                                <dd class="text-xs text-gray-500">{{ $facture->prochaine_relance_at->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                    @endif
                    @if($facture->date_paiement)
                        <div class="flex justify-between"><dt class="text-gray-400">Payée le</dt><dd class="text-green-600 font-medium">{{ $facture->date_paiement->format('d/m/Y') }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Client</h3>
                <a href="{{ route('clients.show', $facture->client) }}" class="font-medium text-blue-600 hover:underline">{{ $facture->client->nom }}</a>
                <div class="text-sm text-gray-500 mt-1">{{ $facture->client->adresse }}<br>{{ $facture->client->code_postal }} {{ $facture->client->ville }}</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Montants</h3>
                <dl class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-600"><dt>Total HT</dt><dd class="font-medium">{{ number_format($facture->montant_ht, 2, ',', ' ') }} €</dd></div>
                    @foreach($totauxTva as $taux => $montant)
                        <div class="flex justify-between text-gray-400 text-xs"><dt>TVA {{ number_format((float)$taux, 0) }}%</dt><dd>{{ number_format($montant, 2, ',', ' ') }} €</dd></div>
                    @endforeach
                    <div class="flex justify-between text-gray-600"><dt>Total TTC</dt><dd class="font-medium">{{ number_format($facture->montant_ttc, 2, ',', ' ') }} €</dd></div>
                    @if($facture->acompte_deduit > 0)
                        <div class="flex justify-between text-gray-400 text-xs"><dt>Acompte déduit</dt><dd>- {{ number_format($facture->acompte_deduit, 2, ',', ' ') }} €</dd></div>
                    @endif
                    @if($facture->retenue_garantie_pct > 0)
                        <div class="flex justify-between text-amber-600 text-xs">
                            <dt>Retenue de garantie ({{ number_format($facture->retenue_garantie_pct, 0) }}%)</dt>
                            <dd>- {{ number_format($facture->retenue_garantie_montant, 2, ',', ' ') }} €</dd>
                        </div>
                    @endif
                    <div class="flex justify-between font-bold text-gray-900 border-t border-gray-200 pt-2 mt-2 text-base">
                        <dt>Net à payer</dt>
                        <dd>{{ number_format($facture->montant_net_a_payer, 2, ',', ' ') }} €</dd>
                    </div>
                    @if($facture->retenue_garantie_pct > 0)
                        @if(!$facture->retenue_garantie_liberee_at)
                            <div class="mt-2 bg-amber-50 border border-amber-200 rounded p-2 flex items-center justify-between gap-3">
                                <span class="text-xs text-amber-700">
                                    Retenue de garantie {{ $facture->retenue_garantie_pct }}% =
                                    <strong>{{ number_format($facture->retenue_garantie_montant, 2, ',', ' ') }} €</strong>
                                    libérable après garantie.
                                </span>
                                @hasanyrole('admin|comptable')
                                <form method="POST" action="{{ route('factures.liberer-retenue', $facture) }}"
                                      onsubmit="return confirm('Libérer la retenue de garantie de {{ number_format($facture->retenue_garantie_montant, 2, \',\', \' \') }} € ?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 bg-amber-600 text-white rounded hover:bg-amber-700 whitespace-nowrap">
                                        Libérer
                                    </button>
                                </form>
                                @endhasanyrole
                            </div>
                        @else
                            <div class="text-xs text-green-600 mt-1 bg-green-50 rounded p-2">
                                Retenue de garantie libérée le {{ $facture->retenue_garantie_liberee_at->format('d/m/Y') }}
                                — {{ number_format($facture->retenue_garantie_montant, 2, ',', ' ') }} € à encaisser.
                            </div>
                        @endif
                    @endif
                    @if($facture->montant_total_paye > 0)
                        <div class="flex justify-between text-green-600"><dt>Encaissé</dt><dd>{{ number_format($facture->montant_total_paye, 2, ',', ' ') }} €</dd></div>
                        @if($facture->montant_restant > 0)
                            <div class="flex justify-between text-red-600 font-medium"><dt>Reste à payer</dt><dd>{{ number_format($facture->montant_restant, 2, ',', ' ') }} €</dd></div>
                        @endif
                    @endif
                </dl>
            </div>

            @if($facture->paiements->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Historique des paiements</h3>
                <div class="space-y-2">
                    @foreach($facture->paiements as $paiement)
                    <div class="flex items-center justify-between text-sm border-b border-gray-100 pb-2 last:border-0">
                        <div>
                            <span class="font-medium text-gray-800">{{ number_format($paiement->montant, 2, ',', ' ') }} €</span>
                            <span class="text-gray-400 ml-2">{{ $paiement->date_paiement->format('d/m/Y') }}</span>
                            @if($paiement->mode)
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded ml-2">{{ $paiement->mode }}</span>
                            @endif
                        </div>
                        @if($paiement->reference)
                            <span class="text-xs text-gray-400">Réf: {{ $paiement->reference }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                <div class="flex justify-between font-semibold text-sm mt-3 pt-2 border-t border-gray-200">
                    <span>Total encaissé</span>
                    <span class="text-green-700">{{ number_format($facture->montant_total_paye, 2, ',', ' ') }} €</span>
                </div>
                @if($facture->montant_restant > 0)
                <div class="flex justify-between text-sm mt-1">
                    <span class="text-gray-400">Reste à encaisser</span>
                    <span class="text-amber-600 font-medium">{{ number_format($facture->montant_restant, 2, ',', ' ') }} €</span>
                </div>
                @endif
            </div>
            @endif

            @if((string) $facture->statut !== 'payee')
                <form method="POST" action="{{ route('factures.destroy', $facture) }}"
                      onsubmit="return confirm('Supprimer cette facture ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 text-sm text-red-500 hover:text-red-700 border border-red-200 rounded-lg hover:bg-red-50">
                        Supprimer
                    </button>
                </form>
            @endif
        </div>

        <div class="col-span-3 lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-700">Détail des prestations</h3>
                </div>
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">Désignation</th>
                            <th class="px-4 py-2 text-right">Qté</th>
                            <th class="px-4 py-2 text-right">Prix HT</th>
                            <th class="px-4 py-2 text-right">TVA</th>
                            <th class="px-4 py-2 text-right">Total HT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($facture->lignes as $ligne)
                            @if($ligne->est_section)
                                <tr class="bg-blue-50"><td colspan="5" class="px-4 py-2 font-semibold text-blue-800 text-xs uppercase">{{ $ligne->designation }}</td></tr>
                            @else
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ $ligne->designation }}</div>
                                        @if($ligne->detail)<div class="text-xs text-gray-400">{{ $ligne->detail }}</div>@endif
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ number_format($ligne->quantite, 2, ',', ' ') }} {{ $ligne->unite }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }} €</td>
                                    <td class="px-4 py-3 text-right text-gray-400">{{ number_format($ligne->taux_tva, 0) }}%</td>
                                    <td class="px-4 py-3 text-right font-medium">{{ number_format($ligne->montant_ht, 2, ',', ' ') }} €</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($facture->notes)
                <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-4">
                    <h3 class="text-sm font-semibold text-yellow-800 mb-1">Notes</h3>
                    <p class="text-sm text-yellow-900 whitespace-pre-wrap">{{ $facture->notes }}</p>
                </div>
            @endif

            {{-- Avoirs liés --}}
            @if($facture->avoirs->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-red-100 flex items-center justify-between">
                        <h3 class="font-semibold text-red-700 text-sm">Avoirs / Notes de crédit</h3>
                        <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">
                            Total déduit : {{ number_format($facture->totalAvoirs(), 2, ',', ' ') }} € TTC
                        </span>
                    </div>
                    <table class="min-w-full text-sm divide-y divide-red-50">
                        <thead class="bg-red-50 text-xs text-red-500 uppercase">
                            <tr>
                                <th class="px-4 py-2 text-left">Numéro</th>
                                <th class="px-4 py-2 text-left">Date</th>
                                <th class="px-4 py-2 text-left">Motif</th>
                                <th class="px-4 py-2 text-right">Montant TTC</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-50">
                            @foreach($facture->avoirs as $avoir)
                                <tr class="hover:bg-red-50">
                                    <td class="px-4 py-3 font-mono text-red-600 font-medium">{{ $avoir->numero }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $avoir->date_document->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-gray-700 truncate max-w-xs">{{ $avoir->motif }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-red-600">
                                        − {{ number_format($avoir->montant_ttc, 2, ',', ' ') }} €
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('avoirs.show', $avoir) }}"
                                           class="text-xs text-gray-400 hover:text-blue-600">Voir</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
