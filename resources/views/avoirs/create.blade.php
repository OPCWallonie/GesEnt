<x-app-layout>
    <x-slot name="header">Émettre un avoir — {{ $facture->numero }}</x-slot>

    <div class="max-w-2xl space-y-6">

        {{-- Info facture d'origine --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
            <div class="font-semibold mb-1">Facture d'origine</div>
            <div class="grid grid-cols-2 gap-2 text-blue-700">
                <div>Numéro : <span class="font-mono font-medium">{{ $facture->numero }}</span></div>
                <div>Client : <strong>{{ $facture->client->nom }}</strong></div>
                <div>Net à payer : <strong>{{ number_format($facture->montant_net_a_payer, 2, ',', ' ') }} €</strong></div>
                <div>Date : {{ $facture->date_document->format('d/m/Y') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('avoirs.store', $facture) }}" class="space-y-6">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <h3 class="font-semibold text-gray-800">Détails de l'avoir</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de l'avoir *</label>
                        <input type="date" name="date_document" value="{{ date('Y-m-d') }}" required
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Taux TVA (%) *</label>
                        <input type="number" name="taux_tva" value="21" step="0.01" min="0" required
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motif de l'avoir *</label>
                    <textarea name="motif" rows="3" required
                              class="w-full rounded-lg border-gray-300 shadow-sm text-sm"
                              placeholder="Ex : Retour de matériel, erreur de facturation, remise commerciale…">{{ old('motif') }}</textarea>
                </div>

                <div x-data="{
                    ht: {{ old('montant_ht', 0) }},
                    tva: 21,
                    get ttc() { return (parseFloat(this.ht) || 0) * (1 + (parseFloat(this.tva) || 0) / 100); }
                }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Montant HT à créditer (€) *</label>
                    <input type="number" name="montant_ht" step="0.01" min="0.01" required
                           x-model="ht"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm"
                           placeholder="0.00">

                    <div class="mt-3 p-3 bg-gray-50 rounded-lg text-sm text-right text-gray-700">
                        Montant TTC : <strong class="text-lg text-gray-900" x-text="ttc.toFixed(2).replace('.', ',') + ' €'"></strong>
                    </div>

                    <p class="text-xs text-gray-500 mt-1">
                        Doit être inférieur ou égal au net à payer de la facture
                        ({{ number_format($facture->montant_net_a_payer, 2, ',', ' ') }} €).
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes internes</label>
                    <textarea name="notes" rows="2"
                              class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="flex justify-between">
                <a href="{{ route('factures.show', $facture) }}"
                   class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">← Retour à la facture</a>
                <button type="submit"
                        class="px-6 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                    Émettre l'avoir
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
