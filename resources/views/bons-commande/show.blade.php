<x-app-layout>
    <x-slot name="header">{{ $bdc->numero }} <x-badge :statut="$bdc->statut"/></x-slot>
    <x-slot name="actions">
        <x-barre-actions>
            <x-slot name="primaires">
                <a href="{{ route('bons-commande.pdf', $bdc) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    PDF
                </a>
                @if($bdc->factures->isEmpty())
                    <a href="{{ route('bons-commande.edit', $bdc) }}"
                       class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                        Modifier
                    </a>
                @endif
            </x-slot>

            <x-slot name="secondaires">
                {{-- Envoyer par email --}}
                <div x-data="{ open: false }">
                    <button @click="open = true"
                            class="w-full inline-flex items-center gap-2 px-3 py-2 border border-indigo-300 text-indigo-600 text-sm rounded-lg hover:bg-indigo-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Envoyer
                    </button>
                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                        <div @click.outside="open = false" class="bg-white rounded-xl shadow-xl p-6 w-[480px] space-y-4">
                            <h3 class="font-semibold text-gray-800">Envoyer le bon de commande par email</h3>
                            <form method="POST" action="{{ route('bons-commande.envoyer', $bdc) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Destinataire *</label>
                                    <input type="email" name="email" required
                                           value="{{ $bdc->client->email ?? '' }}"
                                           class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Message (optionnel)</label>
                                    <textarea name="message" rows="5"
                                              class="w-full rounded-lg border-gray-300 text-sm">{{ $messageEmailDefaut ?? '' }}</textarea>
                                </div>
                                <p class="text-xs text-gray-400">Le PDF du bon de commande sera joint automatiquement.</p>
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
                <a href="{{ route('bons-commande.avenants.create', $bdc) }}"
                   class="w-full inline-flex items-center gap-2 px-3 py-2 border border-indigo-300 text-indigo-700 text-sm rounded-lg hover:bg-indigo-50">
                    + Avenant
                </a>
                @if($bdc->peutEtreFacture())
                    <form method="POST" action="{{ route('bons-commande.facturer', $bdc) }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                            Facturer sit. n°{{ $bdc->prochainNumeroSituation() }}
                            ({{ number_format($bdc->pourcentageRestant(), 0) }}% restant) →
                        </button>
                    </form>
                @endif
                @foreach($bdc->factures as $f)
                    <a href="{{ route('factures.show', $f) }}"
                       class="w-full inline-flex items-center gap-2 px-3 py-2 bg-green-100 text-green-700 text-sm rounded-lg hover:bg-green-200">
                        Sit. {{ $f->numero_situation }} — {{ $f->numero }}
                    </a>
                @endforeach
            </x-slot>
        </x-barre-actions>
    </x-slot>

    <div class="grid grid-cols-3 gap-6">
        {{-- Infos --}}
        <div class="col-span-3 lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Informations</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-400">Numéro</dt><dd class="font-mono font-medium">{{ $bdc->numero }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Date</dt><dd>{{ $bdc->date_document->format('d/m/Y') }}</dd></div>
                    @if($bdc->date_debut_travaux)
                        <div class="flex justify-between"><dt class="text-gray-400">Début travaux</dt><dd>{{ $bdc->date_debut_travaux->format('d/m/Y') }}</dd></div>
                    @endif
                    @if($bdc->date_fin_prevue)
                        <div class="flex justify-between"><dt class="text-gray-400">Fin prévue</dt><dd>{{ $bdc->date_fin_prevue->format('d/m/Y') }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-gray-400">Statut</dt><dd><x-badge :statut="$bdc->statut"/></dd></div>
                    @if($bdc->devis)
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Devis origine</dt>
                            <dd><a href="{{ route('devis.show', $bdc->devis) }}" class="text-blue-600 hover:underline font-mono text-xs">{{ $bdc->devis->numero }}</a></dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Client</h3>
                <a href="{{ route('clients.show', $bdc->client) }}" class="font-medium text-blue-600 hover:underline">{{ $bdc->client->nom }}</a>
                <div class="text-sm text-gray-500 mt-1">{{ $bdc->client->adresse }}<br>{{ $bdc->client->code_postal }} {{ $bdc->client->ville }}</div>
                @if($bdc->chantier)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-400">Chantier</p>
                        <a href="{{ route('chantiers.show', $bdc->chantier) }}" class="text-sm text-blue-600 hover:underline">{{ $bdc->chantier->nom }}</a>
                    </div>
                @endif
            </div>

            {{-- Totaux cumulés --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Totaux (BDC + avenants)</h3>
                <dl class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-600"><dt>Total HT</dt><dd class="font-medium">{{ number_format($totaux['ht'], 2, ',', ' ') }} €</dd></div>
                    <div class="flex justify-between text-gray-400 text-xs"><dt>TVA</dt><dd>{{ number_format($totaux['ttc'] - $totaux['ht'], 2, ',', ' ') }} €</dd></div>
                    @if($totaux['frais_port'] > 0)
                        <div class="flex justify-between text-gray-400 text-xs"><dt>Frais de port</dt><dd>{{ number_format($totaux['frais_port'], 2, ',', ' ') }} €</dd></div>
                    @endif
                    <div class="flex justify-between font-semibold text-gray-900 border-t border-gray-200 pt-2 mt-2"><dt>Total TTC</dt><dd>{{ number_format($totaux['ttc'], 2, ',', ' ') }} €</dd></div>
                    @if($totaux['acompte'] > 0)
                        <div class="flex justify-between text-gray-400 text-xs"><dt>Acompte total</dt><dd>{{ number_format($totaux['acompte'], 2, ',', ' ') }} €</dd></div>
                    @endif
                </dl>
            </div>

            @if($bdc->factures->isEmpty())
                <form method="POST" action="{{ route('bons-commande.destroy', $bdc) }}"
                      onsubmit="return confirm('Supprimer ce BDC et tous ses avenants ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 text-sm text-red-500 hover:text-red-700 border border-red-200 rounded-lg hover:bg-red-50">
                        Supprimer
                    </button>
                </form>
            @endif
        </div>

        {{-- Contenu --}}
        <div class="col-span-3 lg:col-span-2 space-y-6">
            {{-- Lignes BDC --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between">
                    <h3 class="font-semibold text-gray-700">Prestations</h3>
                    <span class="text-sm text-gray-400">{{ number_format($bdc->montant_ttc, 2, ',', ' ') }} € TTC</span>
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
                        @foreach($bdc->lignes as $ligne)
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

            {{-- Situations facturées --}}
            @if($bdc->factures->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-green-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-green-100 flex items-center justify-between">
                    <h3 class="font-semibold text-green-700 text-sm">Situations facturées</h3>
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">
                        {{ number_format($bdc->pourcentageFacture(), 0) }}% facturé
                    </span>
                </div>
                <div class="divide-y divide-green-50">
                    @foreach($bdc->factures as $facture)
                    <div class="flex items-center justify-between px-5 py-3 hover:bg-green-50">
                        <div>
                            <a href="{{ route('factures.show', $facture) }}" class="font-medium text-green-700 hover:underline text-sm">
                                Sit. {{ $facture->numero_situation }} — {{ $facture->numero }}
                            </a>
                            <x-badge :statut="$facture->statut" />
                        </div>
                        <div class="text-right text-sm">
                            @if($facture->pourcentage_avancement)
                                <span class="text-gray-500 text-xs">{{ number_format($facture->pourcentage_avancement, 0) }}%</span>
                            @endif
                            <span class="font-medium text-gray-700 ml-2">{{ number_format($facture->montant_ttc, 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="px-5 py-3 bg-green-50 flex justify-between text-sm font-semibold text-green-800">
                    <span>Total facturé</span>
                    <span>{{ number_format($bdc->montantFacture(), 2, ',', ' ') }} €
                          @if($bdc->pourcentageRestant() > 0)
                              <span class="text-xs font-normal text-gray-500">
                                  ({{ number_format($bdc->montantRestant(), 2, ',', ' ') }} € restant)
                              </span>
                          @endif
                    </span>
                </div>
            </div>
            @endif

            {{-- Avenants --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-700">Avenants ({{ $bdc->avenants->count() }})</h3>
                    @if($bdc->factures->isEmpty())
                        <a href="{{ route('bons-commande.avenants.create', $bdc) }}"
                           class="text-sm text-indigo-600 hover:underline">+ Ajouter un avenant</a>
                    @endif
                </div>
                @forelse($bdc->avenants as $avenant)
                    <div class="flex items-center justify-between px-5 py-3 border-b last:border-0 hover:bg-gray-50">
                        <div>
                            <span class="font-mono text-sm font-medium text-gray-800">{{ $avenant->numero }}</span>
                            @if($avenant->objet) <span class="text-sm text-gray-500 ml-2">— {{ $avenant->objet }}</span> @endif
                            <x-badge :statut="$avenant->statut" />
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-gray-700">{{ number_format($avenant->montant_ttc, 2, ',', ' ') }} €</span>
                            <a href="{{ route('avenants.edit', $avenant) }}" class="text-gray-400 hover:text-gray-600 text-xs">Modifier</a>
                            <form method="POST" action="{{ route('avenants.destroy', $avenant) }}"
                                  onsubmit="return confirm('Supprimer cet avenant ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 text-xs">Supprimer</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-gray-400">Aucun avenant.</p>
                @endforelse
            </div>

            @if($bdc->notes)
                <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-4">
                    <h3 class="text-sm font-semibold text-yellow-800 mb-1">Notes</h3>
                    <p class="text-sm text-yellow-900 whitespace-pre-wrap">{{ $bdc->notes }}</p>
                </div>
            @endif

            {{-- Historique des envois email --}}
            @if($bdc->emailEnvois->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Historique des envois email</h3>
                <div class="space-y-2">
                    @foreach($bdc->emailEnvois as $envoi)
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
    </div>
</x-app-layout>
