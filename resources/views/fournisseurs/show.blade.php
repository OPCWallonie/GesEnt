<x-app-layout>
    <x-slot name="header">{{ $fournisseur->nom }}</x-slot>
    <x-slot name="actions">
        <a href="{{ route('factures-achat.create', ['fournisseur_id' => $fournisseur->id]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            + Facture achat
        </a>
        <a href="{{ route('fournisseurs.edit', $fournisseur) }}"
           class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
            Modifier
        </a>
    </x-slot>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-3 lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Coordonnées</h3>
                <dl class="space-y-2 text-sm">
                    @if($fournisseur->contact)
                        <div><dt class="text-gray-400 text-xs">Contact</dt><dd class="text-gray-800">{{ $fournisseur->contact }}</dd></div>
                    @endif
                    @if($fournisseur->email)
                        <div><dt class="text-gray-400 text-xs">Email</dt>
                            <dd><a href="mailto:{{ $fournisseur->email }}" class="text-blue-600 hover:underline">{{ $fournisseur->email }}</a></dd>
                        </div>
                    @endif
                    @if($fournisseur->telephone)
                        <div><dt class="text-gray-400 text-xs">Tél.</dt><dd class="text-gray-800">{{ $fournisseur->telephone }}</dd></div>
                    @endif
                    @if($fournisseur->adresse || $fournisseur->ville)
                        <div><dt class="text-gray-400 text-xs">Adresse</dt>
                            <dd class="text-gray-800">
                                @if($fournisseur->adresse){{ $fournisseur->adresse }}<br>@endif
                                {{ $fournisseur->code_postal }} {{ $fournisseur->ville }}
                            </dd>
                        </div>
                    @endif
                    @if($fournisseur->numero_tva)
                        <div><dt class="text-gray-400 text-xs">N° TVA</dt><dd class="font-mono text-gray-800">{{ $fournisseur->numero_tva }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Synthèse</h3>
                <div class="space-y-3">
                    <div>
                        <div class="text-xs text-gray-400">Total achats TTC</div>
                        <div class="text-2xl font-bold text-gray-800">{{ number_format($totalTTC, 2, ',', ' ') }} €</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400">En cours (à payer)</div>
                        <div class="text-lg font-semibold {{ $totalEnCours > 0 ? 'text-orange-600' : 'text-gray-400' }}">
                            {{ number_format($totalEnCours, 2, ',', ' ') }} €
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400">Nb de factures</div>
                        <div class="text-lg font-semibold text-gray-700">{{ $fournisseur->factures_achat_count }}</div>
                    </div>
                </div>
            </div>

            @if($fournisseur->notes)
                <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-4">
                    <h3 class="text-sm font-semibold text-yellow-800 mb-1">Notes</h3>
                    <p class="text-sm text-yellow-900 whitespace-pre-wrap">{{ $fournisseur->notes }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('fournisseurs.destroy', $fournisseur) }}"
                  onsubmit="return confirm('Supprimer ce fournisseur ?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full px-4 py-2 text-sm text-red-500 hover:text-red-700 border border-red-200 rounded-lg hover:bg-red-50">
                    Supprimer
                </button>
            </form>
        </div>

        <div class="col-span-3 lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <h3 class="font-semibold text-gray-700">Factures d'achats</h3>
                    <a href="{{ route('factures-achat.create', ['fournisseur_id' => $fournisseur->id]) }}"
                       class="text-sm text-blue-600 hover:underline">+ Nouvelle</a>
                </div>
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">Numéro</th>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Catégorie</th>
                            <th class="px-4 py-2 text-left">Chantier</th>
                            <th class="px-4 py-2 text-right">Montant TTC</th>
                            <th class="px-4 py-2 text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($derniereFactures as $fa)
                            <tr class="hover:bg-gray-50 {{ $fa->estEnRetard() ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-3">
                                    <a href="{{ route('factures-achat.show', $fa) }}" class="font-mono text-sm font-medium text-gray-800 hover:text-blue-600">
                                        {{ $fa->numero }}
                                    </a>
                                    @if($fa->reference_fournisseur)
                                        <div class="text-xs text-gray-400">Réf: {{ $fa->reference_fournisseur }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $fa->date_document->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $fa->label_categorie }}</td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ $fa->chantier?->nom ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ number_format($fa->montant_ttc, 2, ',', ' ') }} €</td>
                                <td class="px-4 py-3 text-center">
                                    @if($fa->statut === 'payee')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Payée</span>
                                    @elseif($fa->estEnRetard())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">En retard</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">En attente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune facture.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
