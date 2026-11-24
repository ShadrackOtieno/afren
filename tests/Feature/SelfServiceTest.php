<?php
namespace Tests\Feature;

use App\Models\{ContactMessage, Professional, RecruitmentRequest, User};
use Illuminate\Support\Facades\{Notification, Password};
use Illuminate\Auth\Notifications\ResetPassword;

class SelfServiceTest extends AfrenTestCase
{
    private function profilePayload(array $o = []): array
    {
        $d = $this->validRegistration($o); unset($d['cv'], $d['consent'], $d['password'], $d['password_confirmation']); return $d;
    }

    public function test_professional_registers_with_password_and_manages_own_profile(): void
    {
        $this->post('/register', $this->validRegistration())->assertRedirect('/my/profile');
        $p = Professional::first(); $before = $p->profile_score;
        $this->assertSame('professional', $p->user->role);
        $this->get('/my/profile')->assertOk()->assertSee('Amina Hassan')->assertSee('Profile score');

        $this->put('/my/profile', $this->profilePayload(['years_experience' => 10, 'research_years' => 5, 'fieldwork_years' => 5, 'skills' => 'A, B, C, D, E, F', 'visible' => 1]))
            ->assertSessionHasNoErrors()->assertRedirect();
        $p->refresh();
        $this->assertSame(10, $p->years_experience);
        $this->assertGreaterThan($before, $p->profile_score);
        $this->assertTrue($p->is_active);

        $this->put('/my/profile', $this->profilePayload())->assertSessionHasNoErrors();
        $this->assertFalse($p->fresh()->is_active); // "visible" unticked = paused
    }

    public function test_email_cannot_be_changed_from_the_profile_and_is_unique_at_registration(): void
    {
        $this->pro(['email' => 'taken@t.test']);
        $this->post('/register', $this->validRegistration(['email' => 'taken@t.test']))->assertSessionHasErrors('email');
        $this->post('/register', $this->validRegistration());
        $this->put('/my/profile', $this->profilePayload(['email' => 'hijack@t.test']))->assertSessionHasNoErrors();
        $this->assertSame('amina@t.test', Professional::where('full_name', 'Amina Hassan')->first()->email);
    }

    public function test_registration_requires_a_strong_confirmed_password(): void
    {
        $this->post('/register', $this->validRegistration(['password' => 'weak', 'password_confirmation' => 'weak']))->assertSessionHasErrors('password');
        $this->post('/register', $this->validRegistration(['password_confirmation' => 'different']))->assertSessionHasErrors('password');
        $this->assertSame(0, Professional::count());
    }

    public function test_login_redirects_each_role_to_its_own_area_and_blocks_others(): void
    {
        $this->post('/register', $this->validRegistration()); auth()->logout();
        $pro = Professional::first()->user; $org = $this->subscriber()->user; $admin = $this->staff('admin');

        $this->post('/login', ['email' => 'amina@t.test', 'password' => 'Secret#1234'])->assertRedirect('/my/profile');
        $this->post('/logout'); $this->assertGuest();
        $this->post('/login', ['email' => 'sub@org.test', 'password' => 'Secret#1234'])->assertRedirect('/portal');
        $this->post('/logout');
        $this->post('/login', ['email' => 'admin@t.test', 'password' => 'Password!123'])->assertRedirect('/admin');
        $this->post('/logout');

        $this->post('/login', ['email' => 'amina@t.test', 'password' => 'wrong'])->assertSessionHasErrors('email'); $this->assertGuest();
        $this->actingAs($pro)->get('/admin')->assertForbidden();
        $this->actingAs($pro)->get('/portal')->assertForbidden();
        $this->actingAs($org)->get('/my/profile')->assertForbidden();
        $this->actingAs($admin)->get('/my/profile')->assertForbidden();
        auth()->logout();
        $this->get('/my/profile')->assertRedirect('/login');
        $this->get('/portal')->assertRedirect('/login');
    }

    public function test_deactivated_accounts_cannot_sign_in(): void
    {
        $org = $this->subscriber(); $org->user->update(['is_active' => false]);
        $this->post('/login', ['email' => 'sub@org.test', 'password' => 'Secret#1234'])->assertSessionHasErrors('email'); $this->assertGuest();
    }

    public function test_password_reset_flow(): void
    {
        Notification::fake();
        $u = $this->subscriber()->user;
        $this->post('/forgot-password', ['email' => 'sub@org.test'])->assertSessionHas('status');
        Notification::assertSentTo($u, ResetPassword::class);
        $this->post('/forgot-password', ['email' => 'nobody@x.org'])->assertSessionHas('status'); // no account enumeration
        $token = Password::createToken($u);
        $this->post('/reset-password', ['token' => $token, 'email' => 'sub@org.test', 'password' => 'Brand#New123', 'password_confirmation' => 'Brand#New123'])->assertRedirect('/login');
        $this->post('/login', ['email' => 'sub@org.test', 'password' => 'Brand#New123'])->assertRedirect('/portal');
    }

    public function test_contact_form_stores_message_and_admin_sees_it(): void
    {
        $this->get('/contact')->assertOk()->assertSee('Send message');
        $this->post('/contact', ['name' => 'Sam', 'email' => 'sam@x.org', 'subject' => 'Packages', 'message' => 'Do you cover Ethiopia?'])->assertSessionHas('status');
        $this->assertSame(1, ContactMessage::count());
        $this->post('/contact', ['name' => 'Bot', 'email' => 'b@x.org', 'subject' => 's', 'message' => 'spam spam spam', 'hp' => 'x'])->assertSessionHasErrors('hp');
        $this->actingAs($this->staff('recruiter'))->get('/admin/messages')->assertOk()->assertSee('Packages');
    }

    public function test_request_form_is_a_multi_step_wizard_and_needs_no_login(): void
    {
        $this->get('/request-recruitment')->assertOk()->assertSee('1. Organization details', false)->assertSee('2. Project details', false)
            ->assertSee('3. Personnel requirements', false)->assertSee('4. Commercial information', false)->assertSee('Review', false);
    }

    public function test_portal_lists_views_and_edits_own_requests(): void
    {
        $org = $this->subscriber(); $this->actingAs($org->user);
        $this->post('/request-recruitment', $this->requestPayload())->assertRedirect('/request-recruitment/received');
        $req = RecruitmentRequest::first();
        $this->get('/portal')->assertOk()->assertSee('Billing history');
        $this->get('/portal/requests')->assertOk()->assertSee($req->reference);
        $this->get("/portal/requests/{$req->reference}")->assertOk()->assertSee('Household survey')->assertSee('Shortlisted candidates');
        $this->get("/portal/requests/{$req->reference}/edit")->assertOk();
        $this->put("/portal/requests/{$req->reference}", $this->requestPayload(['project_title' => 'Updated title']))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Updated title', $req->fresh()->project_title);

        $req->update(['status' => 'paid']);
        $this->get("/portal/requests/{$req->reference}/edit")->assertForbidden();
        $this->put("/portal/requests/{$req->reference}", $this->requestPayload())->assertForbidden();
    }

    public function test_portal_is_isolated_per_organisation_and_hides_direct_requests(): void
    {
        $a = $this->subscriber([], 'a@org.test'); $b = $this->subscriber(['slug' => 'other-plan'], 'b@org.test');
        $this->actingAs($a->user)->post('/request-recruitment', $this->requestPayload());
        $mine = RecruitmentRequest::first();
        auth()->logout();
        $this->post('/request-recruitment', $this->requestPayload(['project_title' => 'Direct one']));
        $direct = RecruitmentRequest::where('source', 'public')->first();

        $this->actingAs($b->user)->get("/portal/requests/{$mine->reference}")->assertNotFound();
        $this->actingAs($a->user)->get("/portal/requests/{$direct->reference}")->assertNotFound();
        $this->get('/portal/requests')->assertDontSee('Direct one')->assertSee($mine->reference);
    }

    public function test_portal_shows_contact_details_only_after_candidate_accepts(): void
    {
        $org = $this->subscriber(); $this->actingAs($org->user);
        $this->post('/request-recruitment', $this->requestPayload());
        $req = RecruitmentRequest::first();
        $pro = $this->pro(['email' => 'secret@t.test']);
        $s = $req->shortlists()->create(['professional_id' => $pro->id, 'job_score' => 80]);
        $url = "/portal/requests/{$req->reference}";
        $this->get($url)->assertSee($pro->full_name)->assertDontSee('secret@t.test');
        $s->update(['status' => 'hired']);
        $this->get($url)->assertSee('secret@t.test');
    }

    public function test_long_public_forms_are_step_by_step_wizards(): void
    {
        $this->get('/register/researcher')->assertOk()->assertSee('wizard(1,', false)
            ->assertSee('Personal &amp; contact', false)->assertSee('Education &amp; experience', false)->assertSee('Skills, languages &amp; availability', false)->assertSee('Documents &amp; login', false)->assertSee('Review &amp; submit', false);
        $p = $this->package();
        $this->get("/packages/{$p->slug}/subscribe")->assertOk()->assertSee('wizard(1,', false)->assertSee('Contact person &amp; login', false)->assertSee('Review &amp; subscribe', false);
        $this->post('/register', $this->validRegistration());
        $this->get('/my/profile')->assertOk()->assertSee('wizard(1,', false)->assertSee('true)', false)->assertSee('Documents &amp; visibility', false); // profile editor: free navigation
    }

    public function test_server_error_reopens_the_step_that_contains_it(): void
    {
        $this->post('/register', $this->validRegistration(['languages' => '']))->assertSessionHasErrors('languages');
        $this->followingRedirects()->post('/register', $this->validRegistration(['languages' => '']))->assertSee('wizard(3,', false);
    }
}
