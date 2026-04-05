<x-app-layout>
    <x-slot name="header">Modifier — {{ $facture->numero }}</x-slot>

    <form method="POST" action="{{ route('factures.update', $facture) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 border-b pb-2">Informations</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de facture *</label>
                    <input type="date" name="date_document" value="{{ old('date_document', $facture->date_document->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date d'échéance</label>
                    <input type="date" name="date_echeance" value="{{ old('date_echeance', $facture->date_echeance?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="statut" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        @foreach(['en_attente' => 'En attente', 'envoyee' => 'Envoyée', 'en_retard' => 'En retard', 'archive' => 'Archivée'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('statut', $facture->statut) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mode de règlement</label>
                    <select name="mode_paiement_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">—</option>
                        @foreach($modesPaiement as $m)
                            <option value="{{ $m->id }}" @selected(old('mode_paiement_id', $facture->mode_paiement_id) == $m->id)>{{ $m->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Délai règlement (jours)</label>
                    <input type="number" name="delai_reglement" value="{{ old('delai_reglement', $facture->delai_reglement) }}" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frais de port (€ HT)</label>
                    <input type="number" name="frais_port" value="{{ old('frais_port', $facture->frais_port) }}" step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ristourne globale (%)</label>
                    <input type="number" name="ristourne_globale" value="{{ old('ristourne_globale', $facture->ristourne_globale) }}" step="0.01" min="0" max="100"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Acompte à déduire (€)</label>
                    <input type="number" name="acompte_deduit" value="{{ old('acompte_deduit', $facture->acompte_deduit) }}" step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Retenue de garantie (%)
                        <span class="text-xs text-gray-400 font-normal">— généralement 5%</span>
                    </label>
                    <input type="number" name="retenue_garantie_pct" value="{{ old('retenue_garantie_pct', $facture->retenue_garantie_pct) }}" step="0.01" min="0" max="100"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de paiement</label>
                    <input type="date" name="date_paiement" value="{{ old('date_paiement', $facture->date_paiement?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Montant encaissé (€)</label>
                    <input type="number" name="montant_paye" value="{{ old('montant_paye', $facture->montant_paye) }}" step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('notes', $facture->notes) }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700">Lignes</h2>
            </div>
            <x-lignes-document :lignes-initiales="$facture->lignes" :taux-tva="$tauxTva" :tva-defaut="21"/>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('factures.show', $facture) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">← Retour</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Enregistrer
            </button>
        </div>
    </form>
</x-app-layout>
