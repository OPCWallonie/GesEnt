<x-app-layout>
    <x-slot name="header">Clients</x-slot>
    <x-slot name="actions">
        <a href="{{ route('clients.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau client
        </a>
    </x-slot>

    <div x-data="liveSearch">
        <form method="GET" class="mb-4 flex gap-3" @submit.prevent="doSearch($el)">
            <div class="relative flex-1">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Rechercher un client…"
                       @input.debounce.300ms="doSearch($el.closest('form'))"
                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 pr-8">
                <div x-show="loading" x-cloak class="absolute right-2 top-1/2 -translate-y-1/2">
                    <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </div>
            </div>
            <a href="{{ route('clients.index') }}" class="px-4 py-2 text-gray-500 text-sm hover:text-gray-700">Effacer</a>
        </form>

        <div id="search-results">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TVA</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Devis</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Factures</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($clients as $client)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('clients.show', $client) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                        {{ $client->nom }}
                                    </a>
                                    @if($client->statut_juridique)
                                        <span class="text-xs text-gray-400 ml-1">{{ $client->statut_juridique }}</span>
                                    @endif
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $client->ville }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $client->email }}<br>
                                    <span class="text-gray-400">{{ $client->telephone }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 font-mono text-xs">{{ $client->numero_tva }}</td>
                                <td class="px-6 py-4 text-right text-gray-600">{{ $client->devis_count }}</td>
                                <td class="px-6 py-4 text-right text-gray-600">{{ $client->factures_count }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('clients.edit', $client) }}" class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                              onsubmit="return confirm('Supprimer ce client ?')">
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
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    Aucun client trouvé.
                                    <a href="{{ route('clients.create') }}" class="text-blue-600 hover:underline ml-1">Créer le premier</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
