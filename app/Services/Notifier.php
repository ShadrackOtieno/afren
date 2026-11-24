<?php
namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Thin wrapper so a mail failure never breaks a user-facing workflow. */
class Notifier
{
    public static function to(string $email, string $subject, string $body): void
    {
        try {
            Mail::raw($body, fn ($m) => $m->to($email)->subject('[AFREN] '.$subject));
        } catch (\Throwable $e) {
            Log::warning('AFREN mail failed: '.$e->getMessage());
        }
    }

    public static function admins(string $subject, string $body, array $roles = ['super_admin', 'admin', 'recruiter']): void
    {
        $emails = array_filter(array_map('trim', explode(',', (string) (Setting::get('notify_emails') ?: env('AFREN_NOTIFY_EMAILS', '')))));
        if (! $emails) {
            $emails = User::where('is_active', true)->whereIn('role', $roles)->pluck('email')->all();
        }
        foreach (array_unique($emails) as $e) { static::to($e, $subject, $body); }
    }
}
