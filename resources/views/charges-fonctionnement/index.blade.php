<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Charges fixes & frais généraux</span>
            <a href="{{ route('charges-fonctionnement.create') }}"
               class="inline-flex items-center gap-1 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                + Nouvelle charge
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- KPI : total mensuel normalisé --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Total mensuel normalisé</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($totalMensuel, 2, ',', ' ') }} €</div>
            <div class="text-xs text-gray-400 mt-1">Charges actives uniquement</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Total annuel estimé</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($totalMensuel * 12, 0, ',', ' ') }} €</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Nombre de charges</div>
            <div class="text-2xl font-bold text-gray-800">{{ $charges->where('actif', true)->count() }}</div>
            <div class="text-xs text-gray-400 mt-1">actives sur {{ $charges->count() }} total</div>
        </div>
    </div>

    @if($charges->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-10 text-center text-sm text-gray-400">
            Aucune charge enregistrée. Ajoutez vos charges fixes pour affiner la rentabilité des chantiers.
        </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full text-sm divide-y divide-gray-100">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Libellé</th>
                    <th class="px-4 py-2 text-left">Catégorie</th>
                    <th class="px-4 py-2 text-right">Montant</th>
                    <th class="px-4 py-2 text-center">Périodicité</th>
                    <th class="px-4 py-2 text-right">Mensuel norm.</th>
                    <th class="px-4 py-2 text-center">Validité</th>
                    <th class="px-4 py-2 text-center">Statut</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($charges as $charge)
                @php
                    $catLabel = \App\Models\ChargeFonctionnement::CATEGORIES[$charge->categorie] ?? $charge->categorie;
                    $periLabel = \App\Models\ChargeFonctionnement::PERIODICITES[$charge->periodicite] ?? $charge->periodicite;
                @endphp
                <tr class="hover:bg-gray-50 {{ $charge->actif ? '' : 'opacity-50' }}">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $charge->libelle }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $catLabel }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-700">
                        {{ number_format($charge->montant_mensuel, 2, ',', ' ') }} €
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $periLabel }}</td>
                    <td class="px-4 py-3 text-right font-medium text-orange-600">
                        {{ number_format($charge->montant_mensuel_normalise, 2, ',', ' ') }} €/mois
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                        {{ $charge->date_debut->format('d/m/Y') }}
                        @if($charge->date_fin)
                            → {{ $charge->date_fin->format('d/m/Y') }}
                        @else
                            → en cours
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($charge->actif)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-green-50 text-green-700">Active</span>
                        @else
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right flex items-center justify-end gap-3">
                        <a href="{{ route('charges-fonctionnement.edit', $charge) }}"
                           class="text-xs text-blue-500 hover:text-blue-700">Modifier</a>
                        <form method="POST" action="{{ route('charges-fonctionnement.destroy', $charge) }}"
                              onsubmit="return confirm('Supprimer cette charge ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-app-layout>
