<?php

namespace App\Support;

/**
 * Helper normalisasi nomor HP Indonesia.
 */
class PhoneHelper
{
    /**
     * Normalisasi: buang semua non-digit, konversi 62 → 0.
     * Contoh: "+62 812-3456-7890" → "081234567890"
     */
    public static function normalize(?string $phone): string
    {
        if ($phone === null) {
            return '';
        }
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        }
        return $digits;
    }

    /**
     * Apakah dua nomor HP merujuk ke nomor yang sama (toleran terhadap 0/62 & format)?
     */
    public static function matches(?string $a, ?string $b): bool
    {
        $na = self::normalize($a);
        $nb = self::normalize($b);

        if ($na === '' || $nb === '') {
            return false;
        }

        return $na === $nb;
    }
}
