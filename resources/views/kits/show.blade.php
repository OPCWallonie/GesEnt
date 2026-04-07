<x-app-layout>
    <x-slot name="header">Kit — {{ $kit->nom }}</x-slot>
    <x-slot name="actions">
        <a href="{{ route('kits.edit', $kit) }}"
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Modifier
        </a>
        <form method="POST" action="{{ route('kits.destroy', $kit) }}"
              onsubmit="return confirm('Supprimer ce kit ?')">
            @csrf @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 border border-red-300 text-red-600 text-sm rounded-lg hover:bg-red-50">
                Supprimer
            </button>
        </form>
    </x-slot>

    <div class="grid grid-cols-3 gap-6">
        {{-- Infos --}}
        <div class="col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
                <h2 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">Informations</h2>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-gray-500">Nom</dt>
                        <dd class="font-medium text-gray-900">{{ $kit->nom }}</dd>
                    </div>
                    @if($kit->categorie)
                    <div>
                        <dt class="text-gray-500">Catégorie</dt>
                        <dd><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ $kit->categorie }}</span></dd>
                    </div>
                    @endif
                    @if($kit->description)
                    <div>
                        <dt class="text-gray-500">Description</dt>
                        <dd class="text-gray-700">{{ $kit->description }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-gray-500">Lignes</dt>
                        <dd class="font-medium text-gray-900">{{ $kit->lignes->count() }} (dont {{ $kit->lignes->where('est_section', false)->count() }} produits)</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Estimation HT</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($kit->estimationHt(), 2, ',', ' ') }} €</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Utilisations</dt>
                        <dd class="font-medium {{ $kit->nb_utilisations > 0 ? 'text-green-600' : 'text-gray-400' }}">{{ $kit->nb_utilisations }}×</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Lignes --}}
        <div class="col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-700">Lignes</h2>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr class="text-xs font-medium text-gray-500 uppercase">
                            <th class="px-4 py-2 text-left">Désignation</th>
                            <th class="px-4 py-2 text-right">Qté</th>
                            <th class="px-4 py-2 text-right">Prix HT</th>
                            <th class="px-4 py-2 text-right">TVA</th>
                            <th class="px-4 py-2 text-right">Total HT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($kit->lignes as $ligne)
                            @if($ligne->est_section)
                                <tr class="bg-blue-50">
                                    <td colspan="5" class="px-4 py-2 font-semibold text-blue-800 text-sm">{{ $ligne->designation }}</td>
                                </tr>
                            @else
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        <div class="font-medium text-gray-800">{{ $ligne->designation }}</div>
                                        @if($ligne->detail)<div class="text-xs text-gray-400">{{ $ligne->detail }}</div>@endif
                                    </td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ number_format($ligne->quantite, 2, ',', '') }} {{ $ligne->unite }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }} €</td>
                                    <td class="px-4 py-2 text-right text-gray-500">{{ $ligne->taux_tva }}%</td>
                                    <td class="px-4 py-2 text-right font-medium text-gray-800">{{ number_format($ligne->quantite * $ligne->prix_unitaire, 2, ',', ' ') }} €</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Total HT estimé</td>
                            <td class="px-4 py-2 text-right font-bold text-gray-900">{{ number_format($kit->estimationHt(), 2, ',', ' ') }} €</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
