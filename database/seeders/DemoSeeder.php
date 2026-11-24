<?php
namespace Database\Seeders;

use App\Models\{Package, Professional};
use App\Services\ScoringService;
use Illuminate\Database\Seeder;

/** OPTIONAL sample data for demos: php artisan db:seed --class=DemoSeeder. Prices are placeholders to be edited by admins. */
class DemoSeeder extends Seeder
{
    public function run(ScoringService $scoring): void
    {
        $pk = [
            ['Starter', 'For occasional needs', 1, 1, 3, ['Access to verified professional pool', 'Up to 1 request per month', 'Up to 3 shortlisted candidates per request'], 0],
            ['Growth', 'For regular fieldwork', 5, 3, 10, ['Everything in Starter', 'Up to 3 requests per month', 'Priority review', 'Up to 10 shortlisted candidates'], 1],
            ['Network', 'For multi-country programmes', null, null, null, ['Everything in Growth', 'Unlimited requests', 'Dedicated recruitment coordinator'], 2],
        ];
        foreach ($pk as [$n, $t, $_p, $req, $cand, $f, $s]) {
            Package::firstOrCreate(['slug' => strtolower($n)], ['name' => $n, 'tagline' => $t, 'price' => [49, 149, 399][$s], 'currency' => 'USD', 'features' => $f,
                'max_requests_per_month' => $req, 'max_candidates_per_request' => $cand, 'sort' => $s, 'is_featured' => $s === 1, 'is_active' => true]);
        }
        $people = [
            ['Amina Hassan', 'KE', '+254', 'researcher', 'master', 7, ['English', 'Swahili'], ['STATA', 'R', 'Qualitative analysis'], ['KoboToolbox', 'ODK']],
            ['Kwame Mensah', 'GH', '+233', 'enumerator', 'bachelor', 4, ['English', 'Twi'], ['Survey administration'], ['KoboToolbox', 'SurveyCTO']],
            ['Fatou Diop', 'SN', '+221', 'both', 'master', 6, ['French', 'Wolof', 'English'], ['SPSS', 'Focus group facilitation'], ['ODK', 'CommCare']],
            ['Tendai Moyo', 'ZW', '+263', 'enumerator', 'diploma', 3, ['English', 'Shona'], ['CAPI'], ['ODK']],
        ];
        foreach ($people as $i => [$name, $c, $d, $cat, $edu, $yrs, $lang, $skills, $tools]) {
            $p = Professional::firstOrCreate(['email' => 'demo'.($i + 1).'@example.org'], [
                'reference' => 'PRO-DEMO'.($i + 1), 'user_id' => \App\Models\User::firstOrCreate(['email' => 'demo'.($i + 1).'@example.org'], ['name' => $name, 'password' => 'DemoPass!123', 'role' => 'professional', 'is_active' => true])->id, 'full_name' => $name, 'dial_code' => $d, 'phone' => '70000000'.$i, 'country_code' => $c, 'category' => $cat,
                'education_level' => $edu, 'years_experience' => $yrs, 'research_years' => max(0, $yrs - 2), 'fieldwork_years' => $yrs - 1,
                'experience_summary' => 'Experienced in household surveys, impact evaluations and community-level data collection across multiple African countries.',
                'skills' => $skills, 'tools' => $tools, 'languages' => $lang, 'availability' => 'available', 'willing_to_travel' => true,
                'preferred_countries' => [$c], 'cv_path' => null, 'verification_status' => 'verified', 'verified_at' => now(), 'consent_at' => now(),
            ]);
            $scoring->refresh($p);
        }
    }
}
