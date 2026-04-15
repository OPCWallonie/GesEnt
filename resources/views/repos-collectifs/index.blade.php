<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <span>Repos compensatoires collectifs</span>
            <div class="flex items-center gap-2">
                @hasanyrole('admin|comptable')
                <a href="{{ route('repos-collectifs.importer') }}"
                   class="border border-gray-300 text-gray-600 text-sm px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">
                    ↑ Importer CSV
                </a>
                <a href="{{ route('repos-collectifs.create') }}"
                   class="inline-flex items-center gap-1 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    + Nouveau RC collectif
                </a>
                @endhasanyrole
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('import_erreurs') && count(session('import_erreurs')) > 0)
        <div class="mb-4 bg-amber-50 border border-amber-200 rounded-lg px-4 py-2 text-sm text-amber-700">
            <div class="font-medium mb-1">Lignes ignorées lors de l'import :</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach(session('import_erreurs') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($reposCollectifs->isEmpty())
            <p class="px-5 py-10 text-sm text-gray-400 text-center">
                Aucun repos compensatoire collectif planifié.
                @hasanyrole('admin|comptable')
                <a href="{{ route('repos-collectifs.create') }}" class="text-blue-500">En créer un</a> ou
                <a href="{{ route('repos-collectifs.importer') }}" class="text-blue-500">importer un calendrier CSV</a>.
                @endhasanyrole
            </p>
        @else
        <table class="min-w-full text-sm divide-y divide-gray-100">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Libellé</th>
                    <th class="px-4 py-2 text-center">Durée</th>
                    <th class="px-4 py-2 text-center">Périmètre</th>
                    <th class="px-4 py-2 text-center">Statut</th>
                    <th class="px-4 py-2 text-right">Absences</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($reposCollectifs as $rc)
                @php
                    $passe = $rc->date->isPast();
                @endphp
                <tr class="hover:bg-gray-50 {{ $passe && ! $rc->applique ? 'opacity-60' : '' }}">
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ ucfirst($rc->date->translatedFormat('l d/m/Y')) }}
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                        <a href="{{ route('repos-collectifs.show', $rc) }}" class="hover:text-blue-600">
                            {{ $rc->libelle }}
                        </a>
                        @if($rc->notes)
                            <div class="text-xs text-gray-400 truncate max-w-xs">{{ $rc->notes }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">
                        {{ $rc->demi_journee ? '½ journée' : '1 jour' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($rc->perimetre === 'tous')
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700">Tous</span>
                        @elseif($rc->perimetre === 'cp')
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-violet-50 text-violet-700">
                                {{ implode(', ', $rc->perimetre_valeurs ?? []) }}
                            </span>
                        @else
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">
                                {{ implode(', ', $rc->perimetre_valeurs ?? []) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($rc->applique)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-green-50 text-green-700">
                                Appliqué
                            </span>
                        @elseif($rc->date->isFuture() || $rc->date->isToday())
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-amber-50 text-amber-600">
                                En attente
                            </span>
                        @else
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">
                                Non appliqué
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-500">
                        {{ $rc->applique ? $rc->absences()->count() : '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('repos-collectifs.show', $rc) }}" class="text-xs text-blue-500 hover:text-blue-700">
                            Détail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    @if($reposCollectifs->hasPages())
        <div class="mt-4">{{ $reposCollectifs->links() }}</div>
    @endif
</x-app-layout>
