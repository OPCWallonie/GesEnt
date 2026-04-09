<x-app-layout>
    <x-slot name="header">Produits &amp; services</x-slot>
    <x-slot name="actions">
        {{-- Import CSV --}}
        <div x-data="{ open: false }">
            <button @click="open = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import CSV
            </button>
            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                <div @click.outside="open = false" class="bg-white rounded-xl shadow-xl p-6 w-[480px] space-y-4">
                    <h3 class="font-semibold text-gray-800">Importer des produits (CSV)</h3>
                    <div class="bg-gray-50 rounded-lg p-3 text-xs text-gray-600 font-mono">
                        Colonnes attendues (séparateur <strong>;</strong> ou <strong>,</strong>) :<br>
                        reference ; designation ; unite ; prix_unitaire ; taux_tva ; categorie ; fournisseur ; reference_fournisseur
                    </div>
                    <form method="POST" action="{{ route('produits.import') }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fichier CSV *</label>
                            <input type="file" name="fichier" accept=".csv,.txt" required
                                   class="w-full text-sm text-gray-700 border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        <p class="text-xs text-gray-400">Les produits avec une référence déjà existante seront ignorés.</p>
                        <div class="flex gap-3 pt-1">
                            <button type="button" @click="open = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Annuler</button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Importer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <a href="{{ route('produits.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau produit
        </a>
    </x-slot>

    <div x-data="liveSearch">
        <form method="GET" class="mb-4 flex gap-3 flex-wrap" @submit.prevent="doSearch($el)">
            <div class="relative flex-1 min-w-48">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Rechercher une désignation…"
                       @input.debounce.300ms="doSearch($el.closest('form'))"
                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 pr-8">
                <div x-show="loading" x-cloak class="absolute right-2 top-1/2 -translate-y-1/2">
                    <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </div>
            </div>
            <select name="categorie"
                    @change="doSearch($el.closest('form'))"
                    class="rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected(request('categorie') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
            <a href="{{ route('produits.index') }}" class="px-4 py-2 text-gray-500 text-sm hover:text-gray-700">Effacer</a>
        </form>

        <div id="search-results">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Désignation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unité</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Prix HT</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">TVA</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($produits as $produit)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $produit->reference ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-900">{{ $produit->designation }}</span>
                                    @if($produit->fournisseur)
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $produit->fournisseur }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-500">{{ $produit->categorie ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $produit->unite }}</td>
                                <td class="px-6 py-4 text-right font-medium text-gray-800">{{ number_format($produit->prix_unitaire, 2, ',', ' ') }} €</td>
                                <td class="px-6 py-4 text-right text-gray-500">{{ (int) $produit->taux_tva }} %</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('produits.edit', $produit) }}" class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('produits.destroy', $produit) }}"
                                              onsubmit="return confirm('Supprimer « {{ $produit->designation }} » ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                    Aucun produit trouvé.
                                    <a href="{{ route('produits.create') }}" class="text-blue-600 hover:underline ml-1">Créer le premier</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100">{{ $produits->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
