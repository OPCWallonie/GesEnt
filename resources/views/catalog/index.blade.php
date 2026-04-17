<x-app-layout>
    <x-slot name="header">Tarifs fournisseurs</x-slot>

    <x-slot name="actions">
        @php
            $depuisUser = auth()->user()->derniere_vue_changements_prix ?? now()->subMonth();
            $nbChangements = \App\Models\CatalogPrixHistorique::significatifs()
                ->where('detected_at', '>', $depuisUser)
                ->count();
        @endphp
        <a href="{{ route('catalog.changements-prix') }}"
           class="relative inline-flex items-center gap-2 px-4 py-2 border border-orange-300 text-orange-700 text-sm font-medium rounded-lg hover:bg-orange-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            Changements de prix
            @if($nbChangements > 0)
                <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-orange-100 text-orange-800">
                    {{ $nbChangements }}
                </span>
            @endif
        </a>
        @role('admin')
        <button @click="$dispatch('open-import')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Importer CSV
        </button>
        @endrole
    </x-slot>

    <div x-data="{ tab: 'catalog' }">

    {{-- Onglets --}}
    <div class="flex gap-1 mb-6 border-b border-gray-200">
        <button @click="tab = 'catalog'"
                :class="tab === 'catalog' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2.5 text-sm font-medium -mb-px">
            Catalogue
            <span class="ml-1.5 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ number_format($totalProduits) }}</span>
        </button>
        @role('admin')
        <button @click="tab = 'config'"
                :class="tab === 'config' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2.5 text-sm font-medium -mb-px">
            Configuration API
        </button>
        @endrole
    </div>

    {{-- === ONGLET CATALOGUE === --}}
    <div x-show="tab === 'catalog'">

        {{-- Filtres --}}
        <form method="GET" action="{{ route('catalog.index') }}" class="flex flex-wrap gap-3 mb-6">
            <div class="relative flex-1 min-w-48">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ $q }}" placeholder="Rechercher référence, désignation, marque…"
                       class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <select name="fournisseur" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Tous les fournisseurs</option>
                @foreach($fournisseurs as $f)
                    <option value="{{ $f }}" @selected($fournisseur === $f)>{{ \App\Models\CatalogProduit::FOURNISSEURS[$f] ?? ucfirst($f) }}</option>
                @endforeach
            </select>
            @if($categories->isNotEmpty())
            <select name="categorie" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected($categorie === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
            @endif
            <label class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="en_stock" value="1" @checked($enStock) class="rounded text-blue-600">
                En stock uniquement
            </label>
            <button type="submit" class="px-4 py-2 bg-gray-700 text-white text-sm rounded-lg hover:bg-gray-800">Filtrer</button>
            @if($q || $fournisseur || $categorie || $enStock)
                <a href="{{ route('catalog.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Effacer</a>
            @endif
        </form>

        {{-- Stats fournisseurs --}}
        @if($configs->isNotEmpty() && !$q && !$fournisseur)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            @foreach($configs as $config)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-semibold text-gray-800">{{ $config->nom_affichage }}</span>
                    <span class="w-2 h-2 rounded-full {{ $config->actif ? 'bg-green-400' : 'bg-gray-300' }}"></span>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($config->nb_produits ?? 0) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    @if($config->derniere_sync)
                        Sync {{ $config->derniere_sync->diffForHumans() }}
                    @else
                        Jamais synchronisé
                    @endif
                </p>
                <div class="flex gap-2 mt-3">
                    <a href="{{ route('catalog.index', ['fournisseur' => $config->fournisseur]) }}"
                       class="text-xs text-blue-600 hover:underline">Voir</a>
                    @role('admin')
                    <button type="button" @click="$dispatch('open-import', { fournisseur: '{{ $config->fournisseur }}' })"
                            class="text-xs text-gray-500 hover:text-gray-700">Import CSV</button>
                    @if($config->url_api && $config->identifiant)
                    <form action="{{ route('catalog.sync') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="fournisseur" value="{{ $config->fournisseur }}">
                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800">Sync API</button>
                    </form>
                    @endif
                    @endrole
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Tableau produits --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if($produits->isEmpty())
                <div class="p-12 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <p class="font-medium">Aucun produit dans le catalogue</p>
                    <p class="text-sm mt-1">Importez un fichier CSV ou configurez une synchronisation API</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Référence</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Désignation</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Catégorie</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Marque</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 uppercase">Prix cat.</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 uppercase">Prix rev.</th>
                            <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $palette = ['bg-blue-100 text-blue-700','bg-purple-100 text-purple-700','bg-orange-100 text-orange-700','bg-green-100 text-green-700','bg-pink-100 text-pink-700','bg-cyan-100 text-cyan-700','bg-amber-100 text-amber-700','bg-indigo-100 text-indigo-700'];
                            $fournisseurIndex = [];
                            foreach($configs as $i => $c) { $fournisseurIndex[$c->fournisseur] = $i; }
                        @endphp
                        @foreach($produits as $produit)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                @php $idx = $fournisseurIndex[$produit->fournisseur] ?? (crc32($produit->fournisseur) % count($palette)); @endphp
                                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full {{ $palette[abs($idx) % count($palette)] }}">
                                    {{ $produit->nom_fournisseur }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $produit->reference }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-900">{{ $produit->designation }}</span>
                                @if($produit->ean)
                                    <span class="block text-xs text-gray-400">EAN: {{ $produit->ean }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 hidden lg:table-cell">{{ $produit->categorie ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ $produit->marque ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">
                                {{ number_format($produit->prix_catalogue, 2, ',', ' ') }} €
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                {{ number_format($produit->prix_revente, 2, ',', ' ') }} €
                                <span class="block text-xs font-normal text-gray-400">TVA {{ $produit->taux_tva }}% / {{ $produit->unite }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($produit->en_stock)
                                    <span class="inline-block w-2 h-2 rounded-full bg-green-400" title="En stock"></span>
                                @else
                                    <span class="inline-block w-2 h-2 rounded-full bg-red-300" title="Rupture"></span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $produits->links() }}
                </div>
            @endif
        </div>
    </div>{{-- /onglet catalog --}}

    {{-- === ONGLET CONFIG === --}}
    @role('admin')
    <div x-show="tab === 'config'" x-cloak>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Fournisseurs existants (tous depuis la DB) --}}
            @forelse($configs as $cfg)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $cfg->nom_affichage }}</h3>
                        <span class="text-xs text-gray-400 font-mono">{{ $cfg->fournisseur }}</span>
                    </div>
                    <span class="text-xs {{ $cfg->actif ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $cfg->actif ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
                <form action="{{ route('catalog.config') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="fournisseur" value="{{ $cfg->fournisseur }}">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nom d'affichage</label>
                        <input type="text" name="nom_affichage" value="{{ $cfg->nom_affichage }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">URL API B2B</label>
                        <input type="url" name="url_api" value="{{ $cfg->url_api }}" placeholder="https://b2b.fournisseur.be/api"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Identifiant</label>
                            <input type="text" name="identifiant" value="{{ $cfg->identifiant }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Mot de passe</label>
                            <input type="password" name="mot_de_passe"
                                   placeholder="{{ $cfg->mot_de_passe ? '••••••••' : 'Non configuré' }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">N° client</label>
                            <input type="text" name="numero_client" value="{{ $cfg->numero_client }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Marge défaut (%)</label>
                            <input type="number" name="marge_defaut" value="{{ $cfg->marge_defaut ?? 0 }}"
                                   step="0.01" min="0" max="200"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" name="actif" value="1" @checked($cfg->actif)
                                   class="rounded text-blue-600">
                            Actif
                        </label>
                        <div class="flex gap-2 items-center">
                            @if($cfg->nb_produits > 0)
                            <form action="{{ route('catalog.vider') }}" method="POST" class="inline"
                                  onsubmit="return confirm('Vider tous les produits de {{ $cfg->nom_affichage }} ?')">
                                @csrf
                                <input type="hidden" name="fournisseur" value="{{ $cfg->fournisseur }}">
                                <button type="submit" class="px-3 py-1.5 text-xs text-orange-600 border border-orange-200 rounded-lg hover:bg-orange-50">
                                    Vider ({{ number_format($cfg->nb_produits) }})
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('catalog.config.delete') }}" method="POST" class="inline"
                                  onsubmit="return confirm('Supprimer définitivement le fournisseur {{ $cfg->nom_affichage }} et tous ses produits ?')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="fournisseur" value="{{ $cfg->fournisseur }}">
                                <button type="submit" class="px-3 py-1.5 text-xs text-red-500 border border-red-200 rounded-lg hover:bg-red-50">
                                    Supprimer
                                </button>
                            </form>
                            <button type="submit" form="form-cfg-{{ $cfg->fournisseur }}"
                                    class="px-4 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @empty
                <div class="col-span-2 text-center text-gray-400 py-8 text-sm">Aucun fournisseur configuré.</div>
            @endforelse

            {{-- Carte "Ajouter un fournisseur" --}}
            <div class="bg-gray-50 rounded-xl border-2 border-dashed border-gray-300 p-6"
                 x-data="{ open: false }">
                <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Ajouter un fournisseur
                </button>
                <form x-show="open" x-cloak action="{{ route('catalog.config') }}" method="POST"
                      class="mt-5 space-y-3">
                    @csrf
                    <div class="bg-blue-50 rounded-lg px-3 py-2 text-xs text-blue-700">
                        L'identifiant est un code court en minuscules (ex : <code>martin_sa</code>, <code>techni_plomb</code>). Il ne peut plus être modifié après création.
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Identifiant unique *</label>
                            <input type="text" name="fournisseur" required
                                   pattern="[a-z0-9_-]+" title="Minuscules, chiffres, tirets et underscores uniquement"
                                   placeholder="ex: martin_sa"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nom d'affichage *</label>
                            <input type="text" name="nom_affichage" required placeholder="ex: Martin S.A."
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">URL API (optionnel)</label>
                            <input type="url" name="url_api" placeholder="https://..."
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Marge défaut (%)</label>
                            <input type="number" name="marge_defaut" value="0" step="0.01" min="0" max="200"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="open = false"
                                class="px-4 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                                class="px-4 py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                            Créer le fournisseur
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    @endrole

    </div>{{-- /x-data tab --}}

    {{-- Modal Import CSV --}}
    @role('admin')
    <div x-data="{ open: false, fournisseur: '' }"
         @open-import.window="open = true; fournisseur = $event.detail?.fournisseur ?? ''"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-gray-900">Importer un catalogue CSV</h2>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('catalog.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur</label>
                    <select name="fournisseur" x-model="fournisseur" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner…</option>
                        @foreach($configs as $cfg)
                            <option value="{{ $cfg->fournisseur }}">{{ $cfg->nom_affichage }}</option>
                        @endforeach
                        @if($configs->isEmpty())
                            <option value="autre">Autre fournisseur</option>
                        @endif
                    </select>
                    @if($configs->isEmpty())
                        <p class="text-xs text-amber-600 mt-1">Aucun fournisseur configuré — allez dans l'onglet "Configuration API" pour en créer un.</p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fichier CSV</label>
                    <input type="file" name="fichier" accept=".csv,.txt" required
                           class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-400 mt-1">Format CSV (séparateur ; ou ,), encodage UTF-8 ou Windows-1252. Max 20 Mo.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Marge de revente (%)</label>
                    <input type="number" name="marge" value="0" step="0.1" min="0" max="200"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">0 = utiliser le prix catalogue comme prix de revente</p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false"
                            class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        Importer
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endrole

</x-app-layout>
