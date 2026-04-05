<x-app-layout>
    <x-slot name="header">Modifier avenant — {{ $avenant->numero }}</x-slot>

    <form method="POST" action="{{ route('avenants.update', $avenant) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 border-b pb-2">Informations</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Objet</label>
                    <input type="text" name="objet" value="{{ old('objet', $avenant->objet) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="statut" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        @foreach(['en_attente' => 'En attente', 'valide' => 'Validé', 'archive' => 'Archivé'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('statut', $avenant->statut) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input type="date" name="date_document" value="{{ old('date_document', $avenant->date_document->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frais de port (€ HT)</label>
                    <input type="number" name="frais_port" value="{{ old('frais_port', $avenant->frais_port) }}" step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Acompte (€)</label>
                    <input type="number" name="acompte" value="{{ old('acompte', $avenant->acompte) }}" step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('notes', $avenant->notes) }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700">Lignes</h2>
            </div>
            <x-lignes-document :lignes-initiales="$avenant->lignes" :taux-tva="$tauxTva" :tva-defaut="21"/>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('bons-commande.show', $avenant->bonCommande) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">← Retour</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Enregistrer
            </button>
        </div>
    </form>
</x-app-layout>
