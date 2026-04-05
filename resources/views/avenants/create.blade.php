<x-app-layout>
    <x-slot name="header">Avenant n°{{ $numeroOrdre }} — {{ $bonCommande->numero }}</x-slot>

    <form method="POST" action="{{ route('bons-commande.avenants.store', $bonCommande) }}" class="space-y-6">
        @csrf

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
            BDC parent : <strong>{{ $bonCommande->numero }}</strong> — Client : <strong>{{ $bonCommande->client->nom }}</strong>
            @if($bonCommande->chantier) — Chantier : {{ $bonCommande->chantier->nom }} @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 border-b pb-2">Informations</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Objet de l'avenant</label>
                    <input type="text" name="objet" value="{{ old('objet') }}" placeholder="Ex: Travaux supplémentaires salle de bain"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input type="date" name="date_document" value="{{ old('date_document', date('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frais de port (€ HT)</label>
                    <input type="number" name="frais_port" value="{{ old('frais_port', 0) }}" step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Acompte (€)</label>
                    <input type="number" name="acompte" value="{{ old('acompte', 0) }}" step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700">Lignes de l'avenant</h2>
            </div>
            <x-lignes-document :lignes-initiales="collect()" :taux-tva="$tauxTva" :tva-defaut="21"/>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('bons-commande.show', $bonCommande) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">← Retour</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                Créer l'avenant
            </button>
        </div>
    </form>
</x-app-layout>
