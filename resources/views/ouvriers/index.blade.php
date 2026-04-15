<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Personnel</span>
            @can('role:admin|comptable')
            <a href="{{ route('ouvriers.create') }}"
               class="inline-flex items-center gap-1 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                + Nouveau membre
            </a>
            @endcan
        </div>
    </x-slot>

    {{-- Filtres --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Nom, prénom…"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none w-52">

        <select name="type_personnel" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">Tous types</option>
            @foreach(\App\Models\Ouvrier::TYPES_PERSONNEL as $key => $label)
                <option value="{{ $key }}" @selected(request('type_personnel') === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="commission_paritaire" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">Toutes CP</option>
            @foreach(\App\Models\Ouvrier::COMMISSIONS_PARITAIRES as $key => $label)
                <option value="{{ $key }}" @selected(request('commission_paritaire') === $key)>{{ $key }}</option>
            @endforeach
        </select>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="desactives" value="1" @checked(request('desactives'))
                   class="rounded border-gray-300 text-blue-600">
            Afficher les désactivés
        </label>

        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-1.5 rounded-lg transition">
            Filtrer
        </button>
        @if(request()->anyFilled(['q','type_personnel','commission_paritaire','desactives']))
            <a href="{{ route('ouvriers.index') }}" class="text-sm text-gray-400 hover:text-gray-600 py-1.5">Réinitialiser</a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($ouvriers->isEmpty())
            <p class="px-5 py-10 text-sm text-gray-400 text-center">Aucun membre du personnel trouvé.</p>
        @else
        <table class="min-w-full text-sm divide-y divide-gray-100">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Nom</th>
                    <th class="px-4 py-2 text-center">Type</th>
                    <th class="px-4 py-2 text-center">Commission paritaire</th>
                    <th class="px-4 py-2 text-right">Coût</th>
                    <th class="px-4 py-2 text-center">Entrée</th>
                    <th class="px-4 py-2 text-center">Statut</th>
                    <th class="px-4 py-2 text-right">Pointages</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($ouvriers as $ouvrier)
                @php
                    $typeBadge = match($ouvrier->type_personnel) {
                        'ouvrier'         => 'bg-blue-50 text-blue-700',
                        'employe_terrain' => 'bg-violet-50 text-violet-700',
                        'employe_admin'   => 'bg-gray-100 text-gray-600',
                        'direction'       => 'bg-amber-50 text-amber-700',
                        default           => 'bg-gray-100 text-gray-500',
                    };
                    $typeLabel = \App\Models\Ouvrier::TYPES_PERSONNEL[$ouvrier->type_personnel] ?? $ouvrier->type_personnel;
                @endphp
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
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBadge }}">
                            {{ $typeLabel }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($ouvrier->categorie)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                {{ $ouvrier->commission_paritaire }} – {{ $ouvrier->categorie }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400">{{ $ouvrier->commission_paritaire }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-gray-700">
                        @if($ouvrier->cout_horaire > 0)
                            {{ number_format($ouvrier->cout_horaire, 2, ',', ' ') }} €/h
                        @elseif($ouvrier->cout_mensuel > 0)
                            {{ number_format($ouvrier->cout_mensuel, 0, ',', ' ') }} €/mois
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $ouvrier->date_entree->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center">
                        @if(! $ouvrier->actif)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-200 text-gray-500">Désactivé</span>
                        @elseif(! $ouvrier->absenceActuelle)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-green-50 text-green-700">Disponible</span>
                        @else
                            @php $abs = $ouvrier->absenceActuelle; @endphp
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs
                                {{ in_array($abs->type, ['maladie','accident_travail'])
                                    ? 'bg-red-50 text-red-600'
                                    : ($abs->type === 'conge'
                                        ? 'bg-sky-50 text-sky-600'
                                        : ($abs->type === 'repos_compensatoire'
                                            ? 'bg-blue-50 text-blue-700'
                                            : 'bg-orange-50 text-orange-600')) }}">
                                {{ $abs->libelle_type }}
                            </span>
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
