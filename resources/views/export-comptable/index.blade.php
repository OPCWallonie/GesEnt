<x-app-layout>
    <x-slot name="header">Export comptable</x-slot>

    <div class="max-w-2xl space-y-6">

        {{-- Info --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
            <p class="font-semibold mb-1">Exports compatibles avec votre logiciel comptable belge</p>
            <p>Générez le journal des ventes ou des achats dans le format de votre logiciel. Remettez le fichier à votre comptable ou importez-le directement.</p>
        </div>

        {{-- Formulaire export --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Paramètres d'export</h2>
            <form method="POST" action="{{ route('export-comptable.export') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de journal</label>
                        <select name="type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="ventes">Journal des ventes (factures clients)</option>
                            <option value="achats">Journal des achats (factures fournisseurs)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logiciel comptable</label>
                        <select name="format" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="winbooks">Winbooks Connect</option>
                            <option value="bob">BOB 50 / Sage BOB</option>
                            <option value="exact">Exact Online</option>
                            <option value="horus">Horus</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Année</label>
                        <select name="annee" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            @foreach($annees as $a)
                                <option value="{{ $a }}" @selected($a == now()->year)>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mois (optionnel)</label>
                        <select name="mois"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Toute l'année</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($m == now()->month)>
                                    {{ \Carbon\Carbon::create()->month($m)->locale('fr')->isoFormat('MMMM') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Télécharger le CSV
                    </button>
                </div>
            </form>
        </div>

        {{-- Export PDF groupé (ZIP pour le comptable) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Export PDF factures (ZIP)</h2>
            <p class="text-sm text-gray-500 mb-4">Téléchargez toutes les factures d'un mois en un seul fichier ZIP — à remettre à votre comptable.</p>
            <form method="GET" action="{{ route('export.factures-pdf-zip') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Année</label>
                    <select name="annee" required
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach(range(now()->year, max(now()->year - 4, 2020)) as $a)
                            <option value="{{ $a }}" @selected($a == now()->year)>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mois</label>
                    <select name="mois" required
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($m == now()->month)>
                                {{ \Carbon\Carbon::create()->month($m)->locale('fr')->isoFormat('MMMM') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Télécharger le ZIP
                </button>
            </form>
        </div>

        {{-- Notes par logiciel --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Comment importer dans votre logiciel</h2>
            <div class="space-y-3 text-sm text-gray-600">
                <div>
                    <span class="font-semibold text-gray-800">Winbooks Connect :</span>
                    Comptabilité → Journaux → Import → Sélectionner le fichier CSV.
                </div>
                <div>
                    <span class="font-semibold text-gray-800">BOB 50 / Sage BOB :</span>
                    Fichier → Importation → Journal → Choisir le fichier CSV généré.
                </div>
                <div>
                    <span class="font-semibold text-gray-800">Exact Online :</span>
                    Comptabilité → Journaux → Importer des écritures → Upload CSV.
                </div>
                <div>
                    <span class="font-semibold text-gray-800">Horus :</span>
                    Données → Import → Journal de ventes/achats → Sélectionner le fichier.
                </div>
                <p class="text-xs text-gray-400 pt-2 border-t border-gray-100">
                    Les codes de journaux (VEN/ACH) et comptes généraux (400000, 700000…) sont des valeurs par défaut.
                    Adaptez-les avec votre comptable selon votre plan comptable.
                </p>
            </div>
        </div>

    </div>
</x-app-layout>
