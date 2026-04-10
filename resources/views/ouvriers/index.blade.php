<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Ouvriers</span>
            <a href="{{ route('ouvriers.create') }}"
               class="inline-flex items-center gap-1 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                + Nouvel ouvrier
            </a>
        </div>
    </x-slot>

    {{-- Filtres --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Nom, prénom…"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none w-52">

        <select name="categorie" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">Toutes catégories</option>
            @foreach(\App\Models\Ouvrier::CATEGORIES as $cat)
                <option value="{{ $cat }}" @selected(request('categorie') === $cat)>Cat. {{ $cat }}</option>
            @endforeach
        </select>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="inactifs" value="1" @checked(request('inactifs'))
                   class="rounded border-gray-300 text-blue-600">
            Inclure inactifs
        </label>

        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-1.5 rounded-lg transition">
            Filtrer
        </button>
        @if(request()->anyFilled(['q','categorie','inactifs']))
            <a href="{{ route('ouvriers.index') }}" class="text-sm text-gray-400 hover:text-gray-600 py-1.5">Réinitialiser</a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($ouvriers->isEmpty())
            <p class="px-5 py-10 text-sm text-gray-400 text-center">Aucun ouvrier trouvé.</p>
        @else
        <table class="min-w-full text-sm divide-y divide-gray-100">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Ouvrier</th>
                    <th class="px-4 py-2 text-center">Catégorie</th>
                    <th class="px-4 py-2 text-right">Coût / h</th>
                    <th class="px-4 py-2 text-center">Entrée</th>
                    <th class="px-4 py-2 text-center">Statut</th>
                    <th class="px-4 py-2 text-right">Pointages</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($ouvriers as $ouvrier)
                <tr class="hover:bg-gray-50 {{ $ouvrier->actif ? '' : 'opacity-60' }}">
                    <td class="px-4 py-3">
                        <a href="{{ route('ouvriers.show', $ouvrier) }}" class="font-medium text-gray-800 hover:text-blue-600">
                            {{ $ouvrier->nom_complet }}
                        </a>
                        @if($ouvrier->email)
                            <div class="text-xs text-gray-400">{{ $ouvrier->email }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                            CP124 – {{ $ouvrier->categorie }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-medium">{{ number_format($ouvrier->cout_horaire, 2, ',', ' ') }} €</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $ouvrier->date_entree->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center">
                        @if(! $ouvrier->actif)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Inactif</span>
                        @elseif($ouvrier->est_disponible)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-green-50 text-green-700">Disponible</span>
                        @else
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-orange-50 text-orange-600">En absence</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ $ouvrier->pointages_count }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('ouvriers.edit', $ouvrier) }}" class="text-xs text-blue-500 hover:text-blue-700">Modifier</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    @if($ouvriers->hasPages())
        <div class="mt-4">{{ $ouvriers->links() }}</div>
    @endif
</x-app-layout>
