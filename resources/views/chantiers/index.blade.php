<x-app-layout>
    <x-slot name="header">Chantiers</x-slot>
    <x-slot name="actions">
        <a href="{{ route('chantiers.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau chantier
        </a>
    </x-slot>

    <div x-data="liveSearch">
        <form method="GET" class="mb-4 flex gap-3 flex-wrap" @submit.prevent="doSearch($el)">
            <div class="relative flex-1 min-w-48">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Rechercher un chantier…"
                       @input.debounce.300ms="doSearch($el.closest('form'))"
                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 pr-8">
                <div x-show="loading" x-cloak class="absolute right-2 top-1/2 -translate-y-1/2">
                    <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </div>
            </div>
            <select name="statut"
                    @change="doSearch($el.closest('form'))"
                    class="rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">Tous les statuts</option>
                <option value="actif"    @selected(request('statut') === 'actif')>Actif</option>
                <option value="inactif"  @selected(request('statut') === 'inactif')>Inactif</option>
                <option value="termine"  @selected(request('statut') === 'termine')>Terminé</option>
                <option value="archive"  @selected(request('statut') === 'archive')>Archivé</option>
            </select>
            <a href="{{ route('chantiers.index') }}" class="px-4 py-2 text-gray-500 text-sm hover:text-gray-700">Effacer</a>
        </form>

        <div id="search-results">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adresse</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($chantiers as $chantier)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('chantiers.show', $chantier) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                        {{ $chantier->nom }}
                                    </a>
                                    @if($chantier->date_debut)
                                        <div class="text-xs text-gray-400 mt-0.5">Début : {{ $chantier->date_debut->format('d/m/Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if($chantier->client)
                                        <a href="{{ route('clients.show', $chantier->client) }}" class="hover:text-blue-600">{{ $chantier->client->nom }}</a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4"><x-badge :statut="$chantier->statut"/></td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $chantier->ville ? $chantier->code_postal.' '.$chantier->ville : '—' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('chantiers.edit', $chantier) }}" class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('chantiers.destroy', $chantier) }}"
                                              onsubmit="return confirm('Supprimer le chantier « {{ $chantier->nom }} » ?')">
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
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    Aucun chantier trouvé.
                                    <a href="{{ route('chantiers.create') }}" class="text-blue-600 hover:underline ml-1">Créer le premier</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100">{{ $chantiers->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
