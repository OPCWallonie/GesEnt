<x-app-layout>
    <x-slot name="header">{{ $avenant->numero }} <x-badge :statut="$avenant->statut"/></x-slot>
    <x-slot name="actions">
        <x-barre-actions>
            <x-slot name="primaires">
                @if($avenant->peutEtreModifie())
                    <a href="{{ route('avenants.edit', $avenant) }}"
                       class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                        Modifier
                    </a>
                @endif
            </x-slot>
            <x-slot name="secondaires">
                @if($avenant->peutEtreArchive())
                    <form method="POST" action="{{ route('avenants.archiver', $avenant) }}"
                          onsubmit="return confirm('Archiver cet avenant ?')">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="w-full border border-gray-300 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                            Archiver
                        </button>
                    </form>
                @endif
            </x-slot>
        </x-barre-actions>
    </x-slot>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-3 lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Informations</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-400">Numéro</dt><dd class="font-mono font-medium">{{ $avenant->numero }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Date</dt><dd>{{ $avenant->date_document->format('d/m/Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Statut</dt><dd><x-badge :statut="$avenant->statut"/></dd></div>
                    @if($avenant->objet)
                        <div class="flex justify-between"><dt class="text-gray-400">Objet</dt><dd class="text-right max-w-48">{{ $avenant->objet }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">BDC parent</h3>
                <a href="{{ route('bons-commande.show', $avenant->bonCommande) }}" class="font-mono text-blue-600 hover:underline font-medium">
                    {{ $avenant->bonCommande->numero }}
                </a>
                <div class="text-sm text-gray-500 mt-1">{{ $avenant->bonCommande->client->nom }}</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Montants</h3>
                <dl class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-600"><dt>Total HT</dt><dd class="font-medium">{{ number_format($avenant->montant_ht, 2, ',', ' ') }} €</dd></div>
                    <div class="flex justify-between text-gray-600"><dt>TVA</dt><dd>{{ number_format($avenant->montant_tva, 2, ',', ' ') }} €</dd></div>
                    <div class="flex justify-between font-bold text-gray-900 border-t border-gray-200 pt-2 mt-2">
                        <dt>Total TTC</dt>
                        <dd>{{ number_format($avenant->montant_ttc, 2, ',', ' ') }} €</dd>
                    </div>
                    @if($avenant->acompte > 0)
                        <div class="flex justify-between text-gray-400 text-xs"><dt>Acompte</dt><dd>{{ number_format($avenant->acompte, 2, ',', ' ') }} €</dd></div>
                    @endif
                </dl>
            </div>

            @if($avenant->peutEtreSupprime())
                <form method="POST" action="{{ route('avenants.destroy', $avenant) }}"
                      onsubmit="return confirm('Supprimer cet avenant ?')">
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
                    <h3 class="font-semibold text-gray-700">Lignes</h3>
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
                        @foreach($avenant->lignes as $ligne)
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

            @if($avenant->notes)
                <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-4">
                    <h3 class="text-sm font-semibold text-yellow-800 mb-1">Notes</h3>
                    <p class="text-sm text-yellow-900 whitespace-pre-wrap">{{ $avenant->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
