<?php
namespace Tests\Feature;

use App\Models\{Package, Professional, User};
use Database\Seeders\{CountrySeeder, EssentialSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

abstract class AfrenTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed([CountrySeeder::class, EssentialSeeder::class]);
    }

    protected function staff(string $role = 'admin'): User
    {
        return User::firstOrCreate(['email' => $role.'@t.test'], ['name' => ucfirst($role), 'password' => 'Password!123', 'role' => $role, 'is_active' => true]);
    }

    protected function package(array $o = []): Package
    {
        return Package::create($o + ['name' => 'Test Plan', 'slug' => 'test-plan'.random_int(1, 99999), 'price' => 100, 'currency' => 'USD', 'features' => ['x'], 'is_active' => true]);
    }

    protected function requestPayload(array $o = []): array
    {
        return $o + [
            'org_name' => 'Acme Research', 'org_type' => 'NGO / Non-profit', 'org_country' => 'KE', 'contact_name' => 'Jane Doe', 'contact_email' => 'jane@acme.org',
            'contact_dial_code' => '+254', 'contact_phone' => '712345678', 'project_title' => 'Household survey', 'project_description' => 'Describe', 'objectives' => 'Objectives',
            'sector' => 'Health', 'project_countries' => ['KE', 'UG'], 'personnel_type' => 'enumerator', 'headcount' => 10, 'min_education' => 'diploma', 'min_years' => 2,
            'languages' => 'English, Swahili', 'technical_skills' => 'Survey administration', 'tools' => 'KoboToolbox', 'consent' => '1',
        ];
    }

    protected function pro(array $o = []): Professional
    {
        static $n = 0; $n++;
        $p = Professional::create($o + ['reference' => 'PRO-T'.$n, 'full_name' => 'Pro '.$n, 'email' => "pro$n@t.test", 'dial_code' => '+254', 'phone' => '7000000'.$n, 'country_code' => 'KE',
            'category' => 'enumerator', 'education_level' => 'bachelor', 'years_experience' => 5, 'research_years' => 2, 'fieldwork_years' => 4, 'languages' => ['English', 'Swahili'],
            'skills' => ['Survey administration'], 'tools' => ['KoboToolbox'], 'availability' => 'available', 'verification_status' => 'verified', 'cv_path' => 'cvs/x.pdf',
            'experience_summary' => str_repeat('Field experience. ', 15)]);
        return app(\App\Services\ScoringService::class)->refresh($p);
    }

    protected function validRegistration(array $o = []): array
    {
        return $o + ['full_name' => 'Amina Hassan', 'email' => 'amina@t.test', 'dial_code' => '+254', 'phone' => '0712 000 111', 'country_code' => 'KE', 'category' => 'both',
            'education_level' => 'master', 'years_experience' => 6, 'research_years' => 4, 'fieldwork_years' => 5, 'languages' => 'English, Swahili', 'skills' => 'STATA, Qualitative',
            'tools' => 'ODK', 'availability' => 'available', 'consent' => '1', 'password' => 'Secret#1234', 'password_confirmation' => 'Secret#1234', 'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf')];
    }

    protected function subscribePayload(array $o = []): array
    {
        return $o + ['name' => 'Org A', 'type' => 'NGO / Non-profit', 'country_code' => 'GH', 'contact_name' => 'Kofi', 'email' => 'kofi@orga.org', 'dial_code' => '+233',
            'phone' => '244000111', 'terms' => '1', 'password' => 'Secret#1234', 'password_confirmation' => 'Secret#1234'];
    }

    /** A signed-up organization account with an ACTIVE subscription. */
    protected function subscriber(array $pkg = [], string $email = 'sub@org.test'): \App\Models\Organization
    {
        $u = User::create(['name' => 'Sub', 'email' => $email, 'password' => 'Secret#1234', 'role' => 'organization', 'is_active' => true]);
        $org = \App\Models\Organization::create(['user_id' => $u->id, 'name' => 'Portal Org', 'contact_name' => 'c', 'email' => $email]);
        \App\Models\Subscription::create(['organization_id' => $org->id, 'package_id' => $this->package($pkg)->id, 'status' => 'active', 'starts_at' => now(), 'renews_at' => now()->addMonth()]);
        return $org;
    }
}
