<?php
namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/** Public contact details and social links (admin-editable in Settings). */
class Site
{
    public const KEYS = ['phone', 'email', 'whatsapp', 'facebook', 'x', 'linkedin', 'instagram', 'youtube'];

    public static function all(): array
    {
        return Cache::rememberForever('afren.site', function () {
            $o = [];
            foreach (self::KEYS as $k) { $o[$k] = trim((string) Setting::get('site_'.$k)); }
            $o['email'] = $o['email'] ?: (string) config('mail.from.address');
            return $o;
        });
    }

    public static function save(array $data): void
    {
        foreach (self::KEYS as $k) { Setting::put('site_'.$k, trim((string) ($data[$k] ?? ''))); }
        Cache::forget('afren.site');
    }

    public static function telHref(string $phone): string { return 'tel:'.preg_replace('/[^\d+]/', '', $phone); }
    public static function waHref(string $n): string { return 'https://wa.me/'.preg_replace('/\D/', '', $n); }

    /** @return array<string,string> platform => url (only those set) */
    public static function socials(): array
    {
        $a = self::all(); $out = [];
        foreach (['facebook' => 'Facebook', 'x' => 'X (Twitter)', 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'youtube' => 'YouTube'] as $k => $label) {
            if ($a[$k]) { $out[$k] = $a[$k]; }
        }
        if ($a['whatsapp']) { $out['whatsapp'] = self::waHref($a['whatsapp']); }
        return $out;
    }

    public static function label(string $k): string
    {
        return ['facebook' => 'Facebook', 'x' => 'X (Twitter)', 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'whatsapp' => 'WhatsApp'][$k] ?? $k;
    }
}
