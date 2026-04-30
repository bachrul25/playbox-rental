<?php

namespace App\Support;

class Rupiah
{
    public static function format(int|float|string|null $value, bool $withSymbol = true): string
    {
        $value = (float) ($value ?? 0);
        $formatted = number_format($value, 0, ',', '.');
        return $withSymbol ? 'Rp' . $formatted : $formatted;
    }
}
