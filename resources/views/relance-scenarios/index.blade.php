<x-app-layout>
    <x-slot name="header">Scénarios de relance</x-slot>
    <x-slot name="actions">
        <a href="{{ route('relance-scenarios.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            + Nouveau scénario
        </a>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="space-y-4">
        @forelse($scenarios as $scenario)
            <div class="bg-white rounded-xl shadow-sm border {{ $scenario->est_defaut ? 'border-indigo-300' : 'border-gray-200' }} p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="font-semibold text-gray-900">{{ $scenario->nom }}</h2>
                            @if($scenario->est_defaut)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                                    Par défaut
                                </span>
                            @endif
                            @if($scenario->factures_count > 0)
                                <span class="text-xs text-gray-400">{{ $scenario->factures_count }} facture(s)</span>
                            @endif
                        </div>
                        @if($scenario->description)
                            <p class="mt-1 text-sm text-gray-500">{{ $scenario->description }}</p>
                        @endif

                        {{-- Étapes --}}
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($scenario->etapes as $etape)
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs border
                                    {{ $etape->ton === 'cordial' ? 'bg-green-50 border-green-200 text-green-700' :
                                       ($etape->ton === 'ferme' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-red-50 border-red-200 text-red-700') }}">
                                    <span class="font-semibold">J+{{ $etape->delai_jours }}</span>
                                    <span>{{ ucfirst($etape->ton) }}</span>
                                    @if($etape->canal === 'courrier')
                                        <span title="Courrier uniquement">✉ PDF</span>
                                    @elseif($etape->canal === 'les_deux')
                                        <span title="Email + courrier PDF">✉+📄</span>
                                    @endif
                                    @unless($etape->actif)
                                        <span class="opacity-50">(inactif)</span>
                                    @endunless
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @unless($scenario->est_defaut)
                            <form method="POST" action="{{ route('relance-scenarios.definir-defaut', $scenario) }}">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs border border-indigo-300 text-indigo-600 rounded-lg hover:bg-indigo-50">
                                    Définir par défaut
                                </button>
                            </form>
                        @endunless
                        <a href="{{ route('relance-scenarios.edit', $scenario) }}"
                           class="px-3 py-1.5 text-xs border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50">
                            Modifier
                        </a>
                        @unless($scenario->est_defaut)
                            <form method="POST" action="{{ route('relance-scenarios.destroy', $scenario) }}"
                                  onsubmit="return confirm('Supprimer le scénario « {{ addslashes($scenario->nom) }} » ?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs border border-red-200 text-red-500 rounded-lg hover:bg-red-50">
                                    Supprimer
                                </button>
                            </form>
                        @endunless
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500">
                <p class="font-medium">Aucun scénario de relance</p>
                <p class="text-sm mt-1">Créez votre premier scénario ou lancez le seeder pour charger le scénario standard.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
