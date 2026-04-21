{{--
    Barre d'actions adaptative pour les fiches document.
    Usage :
        <x-barre-actions>
            <x-slot name="primaires">
                <a href="...">Éditer</a>
                <a href="...">PDF</a>
            </x-slot>
            <x-slot name="secondaires">
                <button ...>Envoyer</button>
                ...
            </x-slot>
        </x-barre-actions>
--}}

@props(['primaires' => null, 'secondaires' => null])

<div class="flex items-center gap-2" x-data="{ menuOuvert: false }">
    {{-- Actions primaires : toujours visibles --}}
    @if($primaires)
        <div class="flex items-center gap-2 flex-wrap">
            {{ $primaires }}
        </div>
    @endif

    {{-- Actions secondaires --}}
    @if($secondaires)
        {{-- Desktop : en ligne --}}
        <div class="hidden md:flex items-center gap-2 flex-wrap">
            {{ $secondaires }}
        </div>

        {{-- Mobile : menu "..." --}}
        <div class="relative md:hidden">
            <button type="button"
                    @click="menuOuvert = !menuOuvert"
                    @click.outside="menuOuvert = false"
                    class="p-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50"
                    aria-label="Plus d'actions">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <circle cx="10" cy="4" r="1.5"/>
                    <circle cx="10" cy="10" r="1.5"/>
                    <circle cx="10" cy="16" r="1.5"/>
                </svg>
            </button>

            <div x-show="menuOuvert"
                 x-transition
                 @click="menuOuvert = false"
                 class="absolute right-0 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-lg z-20 py-1 flex flex-col"
                 x-cloak>
                {{ $secondaires }}
            </div>
        </div>
    @endif
</div>
