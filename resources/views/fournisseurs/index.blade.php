<x-app-layout>
    <x-slot name="header">Fournisseurs</x-slot>
    <x-slot name="actions">
        <a href="{{ route('fournisseurs.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau fournisseur
        </a>
    </x-slot>

    <form method="GET" class="mb-4 flex gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, N° TVA…"
               class="flex-1 rounded-lg border-gray-300 shadow-sm text-sm">
        <label class="flex items-center gap-2 text-sm text-gray-600 px-3 py-2 bg-white border border-gray-300 rounded-lg cursor-pointer">
            <input type="checkbox" name="inactifs" {{ request()->has('inactifs') ? 'checked' : '' }}> Inclure inactifs
        </label>
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Filtrer</button>
        @if(request()->anyFilled(['q']) || request()->has('inactifs'))
            <a href="{{ route('fournisseurs.index') }}" class="px-4 py-2 text-gray-500 text-sm hover:text-gray-700">Effacer</a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° TVA</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Factures</th>
                    <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($fournisseurs as $f)
                    <tr class="hover:bg-gray-50 {{ !$f->actif ? 'opacity-60' : '' }}">
                        <td class="px-5 py-4">
                            <a href="{{ route('fournisseurs.show', $f) }}" class="font-medium text-gray-900 hover:text-blue-600">{{ $f->nom }}</a>
                            @if($f->ville)<div class="text-xs text-gray-400">{{ $f->ville }}</div>@endif
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            {{ $f->contact }}
                            @if($f->email)<div class="text-xs text-gray-400">{{ $f->email }}</div>@endif
                        </td>
                        <td class="px-5 py-4 font-mono text-gray-600 text-xs">{{ $f->numero_tva ?? '—' }}</td>
                        <td class="px-5 py-4 text-right text-gray-700">{{ $f->factures_achat_count }}</td>
                        <td class="px-5 py-4 text-center">
                            @if($f->actif)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Actif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactif</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('fournisseurs.show', $f) }}" class="text-gray-400 hover:text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('fournisseurs.edit', $f) }}" class="text-gray-400 hover:text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">Aucun fournisseur.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-gray-100">{{ $fournisseurs->links() }}</div>
    </div>
</x-app-layout>
