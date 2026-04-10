@if(isset($variation) && $variation !== null && isset($reference) && $reference > 0)
    @php
        $hausse = isset($inverser) && $inverser ? $variation < 0 : $variation > 0;
        $color  = $hausse ? 'text-green-600' : 'text-red-500';
        $arrow  = $hausse ? '↑' : '↓';
    @endphp
    <div class="text-xs {{ $color }} mt-1.5 font-medium">
        {{ $arrow }} {{ $hausse ? '+' : '' }}{{ number_format($variation, 1) }}%
        <span class="text-gray-400 font-normal">vs {{ $anneeN1 }} ({{ number_format($reference, 0, ',', ' ') }} €)</span>
    </div>
@elseif(isset($anneeN1))
    <div class="text-xs text-gray-300 mt-1.5">— vs {{ $anneeN1 }}</div>
@endif
