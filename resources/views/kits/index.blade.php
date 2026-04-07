<x-app-layout>
    <x-slot name="header">Kits / Modèles de lignes</x-slot>
    <x-slot name="actions">
        <a href="{{ route('kits.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau kit
        </a>
    </x-slot>

    {{-- Filtres --}}
    <form method="GET" class="flex gap-3 mb-6">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher par nom…"
               class="w-64 rounded-lg border-gray-300 shadow-sm text-sm">
        @if($categories->isNotEmpty())
        <select name="categorie" class="rounded-lg border-gray-300 shadow-sm text-sm">
            <option value="">Toutes les catégories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" @selected(request('categorie') === $cat)>{{ $cat }}</option>
            @endforeach
        </select>
        @endif
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">Filtrer</button>
        @if(request('q') || request('categorie'))
            <a href="{{ route('kits.index') }}" class="px-4 py-2 text-gray-500 text-sm hover:text-gray-700">Réinitialiser</a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($kits->isEmpty())
            <div class="text-center py-16">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-gray-500 text-sm mb-4">Aucun kit créé pour le moment.</p>
                <a href="{{ route('kits.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                    Créer mon premier kit
                </a>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="text-xs font-medium text-gray-500 uppercase">
                        <th class="px-4 py-3 text-left">Nom</th>
                        <th class="px-4 py-3 text-left">Catégorie</th>
                        <th class="px-4 py-3 text-right">Lignes</th>
                        <th class="px-4 py-3 text-right">Estimation HT</th>
                        <th class="px-4 py-3 text-right">Utilisations</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($kits as $kit)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('kits.show', $kit) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                {{ $kit->nom }}
                            </a>
                            @if($kit->description)
                                <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ $kit->description }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($kit->categorie)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ $kit->categorie }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ $kit->lignes_count }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($kit->estimationHt(), 2, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right">
                            @if($kit->nb_utilisations > 0)
                                <span class="text-green-600 font-medium">{{ $kit->nb_utilisations }}×</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('kits.edit', $kit) }}" class="text-gray-400 hover:text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('kits.destroy', $kit) }}"
                                      onsubmit="return confirm('Supprimer le kit « {{ addslashes($kit->nom) }} » ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($kits->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">{{ $kits->links() }}</div>
            @endif
        @endif
    </div>
</x-app-layout>
