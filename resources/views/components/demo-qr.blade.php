{{-- Декоративный «QR-код» для демонстрационной оплаты по СБП (не настоящий платёжный QR) --}}
@props(['seed'])

@php
    $size = 29;
    $cells = [];
    mt_srand(crc32($seed));

    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            // Три «уголка» как у настоящего QR-кода
            $inFinder = ($x < 7 && $y < 7) || ($x > 21 && $y < 7) || ($x < 7 && $y > 21);

            if ($inFinder) {
                $fx = $x > 21 ? $x - 22 : $x;
                $fy = $y > 21 ? $y - 22 : $y;
                $filled = $fx === 0 || $fx === 6 || $fy === 0 || $fy === 6 || ($fx >= 2 && $fx <= 4 && $fy >= 2 && $fy <= 4);
            } else {
                $filled = mt_rand(0, 1) === 1;
            }

            if ($filled) {
                $cells[] = [$x, $y];
            }
        }
    }
@endphp

<div {{ $attributes->class(['qr-mock']) }} aria-label="Демонстрационный QR-код">
    <svg viewBox="0 0 {{ $size }} {{ $size }}" shape-rendering="crispEdges">
        @foreach ($cells as [$x, $y])
            <rect x="{{ $x }}" y="{{ $y }}" width="1" height="1" fill="#1b1f22"/>
        @endforeach
    </svg>
</div>
