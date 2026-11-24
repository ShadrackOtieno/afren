<?php
namespace Database\Seeders;

use App\Models\{ScoringCriterion, Setting, User};
use App\Services\ScoringService;
use Illuminate\Database\Seeder;

class EssentialSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ScoringService::DEFAULTS as $i => [$key, $label, $desc, $w]) {
            ScoringCriterion::firstOrCreate(['key' => $key], ['label' => $label, 'description' => $desc, 'weight' => $w, 'sort' => $i, 'is_active' => true]);
        }
        Setting::firstOrCreate(['key' => 'min_eligible_score'], ['value' => 0]);

        User::firstOrCreate(['email' => env('AFREN_ADMIN_EMAIL', 'admin@afren.test')], [
            'name' => env('AFREN_ADMIN_NAME', 'AFREN Admin'), 'password' => env('AFREN_ADMIN_PASSWORD', 'ChangeMe!12345'),
            'role' => 'super_admin', 'is_active' => true,
        ]);
    }
}
