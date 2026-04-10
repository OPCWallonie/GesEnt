<x-app-layout>
    <x-slot name="header">Coûts main d'œuvre par chantier</x-slot>

    {{-- Filtres --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <select name="annee" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            @for($y = now()->year; $y >= now()->year - 3; $y--)
                <option value="{{ $y }}" @selected($annee == $y)>{{ $y }}</option>
            @endfor
        </select>

        <select name="mois" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">Toute l'année</option>
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}" @selected($mois == $m)>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-1.5 rounded-lg transition">
            Filtrer
        </button>
    </form>

    {{-- KPIs --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase mb-1">Chantiers avec MO</div>
            <div class="text-3xl font-bold text-gray-800">{{ $parChantier->count() }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase mb-1">Total heures</div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($totalHeures, 1) }}h</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase mb-1">Coût total MO</div>
            <div class="text-2xl font-bold text-orange-600">{{ number_format($totalCout, 0, ',', ' ') }} €</div>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="space-y-4">
        @forelse($parChantier as $item)
        <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <button @click="open = !open"
                    class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition text-left">
                <div class="flex items-center gap-4">
                    <div>
                        <div class="font-semibold text-gray-800">{{ $item['chantier']->nom }}</div>
                        <div class="text-xs text-gray-400">{{ $item['chantier']->client?->nom ?? '—' }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <div class="text-sm font-bold text-gray-800">{{ number_format($item['heures'] + $item['heures_sup'], 1) }}h</div>
                        <div class="text-xs text-gray-400">dont {{ number_format($item['heures_sup'], 1) }}h sup</div>
                    </div>
                    <div class="text-right min-w-[100px]">
                        <div class="text-base font-bold text-orange-600">{{ number_format($item['cout_total'], 0, ',', ' ') }} €</div>
                    </div>
                    <svg x-bind:class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>

            <div x-show="open" x-collapse class="border-t border-gray-100">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-5 py-2 text-left">Ouvrier</th>
                            <th class="px-4 py-2 text-center">Cat.</th>
                            <th class="px-4 py-2 text-right">Jours</th>
                            <th class="px-4 py-2 text-right">Heures</th>
                            <th class="px-4 py-2 text-right">H. sup</th>
                            <th class="px-4 py-2 text-right">Coût</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($item['ouvriers'] as $ligne)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-2 font-medium">
                                <a href="{{ route('ouvriers.show', $ligne['ouvrier']) }}" class="text-gray-800 hover:text-blue-600">
                                    {{ $ligne['ouvrier']->nom_complet }}
                                </a>
                            </td>
                            <td class="px-4 py-2 text-center text-xs text-gray-500">{{ $ligne['ouvrier']->categorie }}</td>
                            <td class="px-4 py-2 text-right text-gray-500">{{ $ligne['nb_pointages'] }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($ligne['heures'], 1) }}h</td>
                            <td class="px-4 py-2 text-right {{ $ligne['heures_sup'] > 0 ? 'text-orange-500' : 'text-gray-300' }}">
                                {{ $ligne['heures_sup'] > 0 ? number_format($ligne['heures_sup'], 1).'h' : '—' }}
                            </td>
                            <td class="px-4 py-2 text-right font-bold text-orange-600">{{ number_format($ligne['cout_total'], 0, ',', ' ') }} €</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-sm text-gray-400">
                Aucun pointage enregistré pour cette période.
            </div>
        @endforelse
    </div>
</x-app-layout>
