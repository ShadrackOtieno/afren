<?php
namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/** Brand colours (admin-editable in Settings) → Tailwind palettes, CSS and SVG tokens. */
class Brand
{
    public const DEFAULTS = ['primary' => '#12355b', 'accent' => '#bf5410']; // dark blue, orange
    private const LEVELS = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];
    // primary = dark base used at level 700; accent = base used at level 500
    private const PRIMARY = ['base' => 700, 'white' => [600 => .12, 500 => .28, 400 => .48, 300 => .68, 200 => .82, 100 => .91, 50 => .96], 'black' => [800 => .2, 900 => .42]];
    private const ACCENT = ['base' => 500, 'white' => [400 => .2, 300 => .4, 200 => .65, 100 => .85, 50 => .93], 'black' => [600 => .12, 700 => .28, 800 => .42, 900 => .55]];

    public static function colors(): array
    {
        return Cache::rememberForever('afren.brand', fn () => [
            'primary' => Setting::get('brand_primary') ?: self::DEFAULTS['primary'],
            'accent' => Setting::get('brand_accent') ?: self::DEFAULTS['accent'],
        ]);
    }

    public static function save(string $primary, string $accent): void
    {
        Setting::put('brand_primary', strtolower($primary)); Setting::put('brand_accent', strtolower($accent)); self::forget();
    }

    public static function reset(): void { Setting::put('brand_primary', ''); Setting::put('brand_accent', ''); self::forget(); }
    public static function forget(): void { Cache::forget('afren.brand'); }
    public static function version(): string { return substr(md5(json_encode(self::colors())), 0, 8); }

    /** @return array{brand: array<int,string>, accent: array<int,string>} */
    public static function palette(): array
    {
        $c = self::colors();
        return ['brand' => self::scale($c['primary'], self::PRIMARY), 'accent' => self::scale($c['accent'], self::ACCENT)];
    }

    private static function scale(string $hex, array $spec): array
    {
        $out = [];
        foreach (self::LEVELS as $l) {
            $out[$l] = $l === $spec['base'] ? $hex : (isset($spec['white'][$l]) ? self::mix($hex, '#ffffff', $spec['white'][$l]) : self::mix($hex, '#000000', $spec['black'][$l]));
        }
        return $out;
    }

    public static function mix(string $a, string $b, float $t): string
    {
        [$ar, $ag, $ab] = self::rgb($a); [$br, $bg, $bb] = self::rgb($b);
        return sprintf('#%02x%02x%02x', round($ar + ($br - $ar) * $t), round($ag + ($bg - $ag) * $t), round($ab + ($bb - $ab) * $t));
    }

    private static function rgb(string $hex): array { $h = ltrim($hex, '#'); return [hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2))]; }

    /** WCAG contrast ratio of white text on this colour. */
    public static function contrastWithWhite(string $hex): float
    {
        $l = 0;
        foreach ([0.2126, 0.7152, 0.0722] as $i => $w) { $v = self::rgb($hex)[$i] / 255; $l += $w * ($v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4); }
        return round(1.05 / ($l + 0.05), 2);
    }

    /** Replace __P700__ / __A500__ tokens in SVG templates. */
    public static function applyToSvg(string $svg): string
    {
        $p = self::palette();
        foreach ($p['brand'] as $l => $hex) { $svg = str_replace("__P{$l}__", $hex, $svg); }
        foreach ($p['accent'] as $l => $hex) { $svg = str_replace("__A{$l}__", $hex, $svg); }
        return $svg;
    }

    public static function svg(string $name): string { return route('brand.svg', ['name' => $name, 'v' => self::version()]); }
}
