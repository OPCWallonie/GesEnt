<x-app-layout>
    <x-slot name="header">Importer un calendrier de RC collectifs (CSV)</x-slot>

    <div class="max-w-2xl space-y-6">

        {{-- Instructions --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 text-sm text-blue-800">
            <div class="font-semibold mb-2">Format attendu du fichier CSV</div>
            <p class="mb-3">Chaque ligne représente un RC collectif. Séparateur : <code class="bg-blue-100 px-1 rounded">;</code> ou <code class="bg-blue-100 px-1 rounded">,</code></p>
            <table class="text-xs w-full border-collapse">
                <thead>
                    <tr class="border-b border-blue-200">
                        <th class="py-1 text-left pr-4">Colonne</th>
                        <th class="py-1 text-left pr-4">Valeur</th>
                        <th class="py-1 text-left">Exemple</th>
                    </tr>
                </thead>
                <tbody class="space-y-1">
                    <tr><td class="py-0.5 pr-4 font-medium">1 — date</td><td class="pr-4">jj/mm/aaaa</td><td>01/05/2026</td></tr>
                    <tr><td class="py-0.5 pr-4 font-medium">2 — libellé</td><td class="pr-4">Texte libre</td><td>Fête du travail</td></tr>
                    <tr><td class="py-0.5 pr-4 font-medium">3 — demi-journée</td><td class="pr-4">0 ou 1</td><td>0</td></tr>
                    <tr><td class="py-0.5 pr-4 font-medium">4 — périmètre</td><td class="pr-4">tous / cp / type</td><td>tous</td></tr>
                </tbody>
            </table>
            <p class="mt-3 text-xs text-blue-600">Les colonnes 3 et 4 sont optionnelles (défaut : 0 et tous).<br>
            Les doublons (même date + libellé) sont ignorés. Un en-tête de colonne est détecté et ignoré automatiquement.</p>
            <div class="mt-3 font-medium text-xs">Exemple :</div>
            <pre class="bg-blue-100 rounded p-2 text-xs mt-1 overflow-x-auto">01/05/2026;Fête du travail;0;tous
21/07/2026;Fête nationale;0;tous
11/11/2026;Armistice;0;cp;CP124,CP149</pre>
        </div>

        {{-- Formulaire --}}
        <form method="POST" action="{{ route('repos-collectifs.importer.post') }}" enctype="multipart/form-data">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fichier CSV <span class="text-red-500">*</span></label>
                    <input type="file" name="fichier_csv" accept=".csv,.txt" required
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    @error('fichier_csv')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-3 mt-4">
                <button type="submit"
                        class="bg-blue-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-blue-700 transition">
                    ↑ Importer
                </button>
                <a href="{{ route('repos-collectifs.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
            </div>
        </form>

    </div>
</x-app-layout>
