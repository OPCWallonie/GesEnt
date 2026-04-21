{{--
    Badge volatilité prix catalogue.
    Props :
      $badge   : VolatiliteBadgeDTO
      $variant : 'compact' | 'standard' | 'detaille'  (défaut : standard)
--}}
@props(['badge', 'variant' => 'standard'])

@php
    if (!$badge || !$badge->visible()) return;

    $couleurs = match($badge->niveau) {
        'warning'    => 'bg-amber-100 text-amber-800 border-amber-200',
        'opportunite'=> 'bg-green-100 text-green-800 border-green-200',
        'info'       => 'bg-blue-100 text-blue-800 border-blue-200',
        default      => 'bg-gray-100 text-gray-700 border-gray-200',
    };
    $couteursForte = match($badge->niveau) {
        'warning'    => 'bg-amber-500',
        'opportunite'=> 'bg-green-500',
        'info'       => 'bg-blue-500',
        default      => 'bg-gray-400',
    };
@endphp

@if($variant === 'compact')
    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-xs rounded border {{ $couleurs }}"
          title="{{ $badge->message }}">
        {{ $badge->icone }}
        @if($badge->signalFort)
            <span class="w-1.5 h-1.5 rounded-full {{ $couteursForte }} inline-block"></span>
        @endif
    </span>

@elseif($variant === 'standard')
    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded border {{ $couleurs }}">
        {{ $badge->icone }}
        <span>{{ $badge->message }}</span>
        @if($badge->signalFort)
            <span class="w-1.5 h-1.5 rounded-full {{ $couteursForte }} ml-0.5 flex-shrink-0"></span>
        @endif
    </span>

@elseif($variant === 'detaille')
    <div class="rounded-lg border p-3 {{ $couleurs }}">
        <div class="flex items-start gap-2">
            <span class="text-lg leading-none flex-shrink-0">{{ $badge->icone }}</span>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium">{{ $badge->message }}</div>
                <div class="text-xs mt-0.5 opacity-75">
                    Classe {{ strtoupper($badge->classe) }}
                    @if($badge->signalFort)
                        · <strong>Signal fort</strong>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
