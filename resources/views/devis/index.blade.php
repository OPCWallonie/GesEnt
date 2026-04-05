<x-app-layout>
    <x-slot name="header">Devis</x-slot>

    <x-slot name="actions">
        <a href="{{ route('export.devis') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
        <a href="{{ route('devis.create') }}"
           class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau devis
        </a>
    </x-slot>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('devis.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-xs font-medium text-gray-600 mb-1">Recherche</label>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Numéro, client, chantier…"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
            <select name="statut"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous les statuts</option>
                <option value="brouillon" {{ request('statut') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                <option value="en_attente" {{ request('statut') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                <option value="valide" {{ request('statut') === 'valide' ? 'selected' : '' }}>Validé</option>
                <option value="refuse" {{ request('statut') === 'refuse' ? 'selected' : '' }}>Refusé</option>
                <option value="expire" {{ request('statut') === 'expire' ? 'selected' : '' }}>Expiré</option>
                <option value="archive" {{ request('statut') === 'archive' ? 'selected' : '' }}>Archivé</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit"
                    class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-medium">
                Filtrer
            </button>
            @if(request('q') || request('statut'))
                <a href="{{ route('devis.index') }}"
                   class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium">
                    Réinitialiser
                </a>
            @endif
        </div>
    </form>

    {{-- Tableau --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numéro</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chantier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validité</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Montant TTC</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($devis as $d)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('devis.show', $d) }}"
                               class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                {{ $d->numero }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $d->client->nom ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $d->chantier->nom ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $d->date_document->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($d->date_validite)
                                <span class="{{ $d->date_validite->isPast() && (string) $d->statut !== 'valide' ? 'text-red-600 font-medium' : '' }}">
                                    {{ $d->date_validite->format('d/m/Y') }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge :statut="$d->statut"/>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right font-medium">
                            {{ number_format($d->montant_ttc ?? 0, 2, ',', ' ') }} €
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('devis.show', $d) }}" class="text-gray-500 hover:text-gray-700" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if((string) $d->statut !== 'archive')
                                    <a href="{{ route('devis.edit', $d) }}" class="text-blue-500 hover:text-blue-700" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('devis.destroy', $d) }}"
                                      onsubmit="return confirm('Supprimer ce devis ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400 text-sm">
                            Aucun devis trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($devis->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $devis->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
