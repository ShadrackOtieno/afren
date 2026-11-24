<?php

namespace App\Support;

class Tags
{
    /** Parse "a, b\nc" into a de-duplicated array of trimmed values. */
    public static function parse(null|string|array $input): array
    {
        $items = is_array($input) ? $input : preg_split('/[,\n;]+/', (string) $input);
        $seen = [];
        foreach ($items as $i) {
            $i = trim((string) $i);
            if ($i !== '') { $seen[mb_strtolower($i)] = $i; }
        }
        return array_values($seen);
    }

    public static function lower(?array $a): array
    {
        return array_map(fn ($x) => mb_strtolower(trim($x)), $a ?? []);
    }
}
