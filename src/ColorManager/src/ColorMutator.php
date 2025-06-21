<?php

declare(strict_types=1);

namespace MoonShine\ColorManager;

use Illuminate\Support\Str;

final class ColorMutator
{
    public static function toHEX(string $value): string
    {
        $result = Str::of($value);

        if ($result->contains('#')) {
            return $result->value();
        }

        return $result
            ->explode(',')
            ->map(static function ($v): string {
                $v = dechex((int) trim($v));

                if (\strlen($v) < 2) {
                    $v = '0' . $v;
                }

                return $v;
            })
            ->prepend('#')
            ->implode('');
    }

    public static function toRGB(string $value): string
    {
        $result = Str::of($value);

        if ($result->contains('#')) {
            $dec = hexdec((string) $result->remove('#')->value());
            $rgb = [
                'red' => 0xFF & ($dec >> 0x10),
                'green' => 0xFF & ($dec >> 0x8),
                'blue' => 0xFF & $dec,
            ];

            return implode(',', $rgb);
        }

        if ($result->contains('rgb')) {
            return $result->remove(['rgb', '(', ')'])
                ->value();
        }

        return $result->value();
    }
}
