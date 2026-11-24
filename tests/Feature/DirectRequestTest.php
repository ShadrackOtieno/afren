<?php
namespace Tests\Feature;

use App\Models\{Organization, RecruitmentRequest, Subscription, User};

class DirectRequestTest extends AfrenTestCase
{
    public function test_public_form_is_available_without_login(): void
    {
        $this->get('/request-recruitment')->assertOk()->assertSee('No account, login, subscription');
    }

    public function test_guest_submits_request_without_account_or_subscription(): void
    {
        $users = User::count();
        $res = $this->post('/request-recruitment', $this->requestPayload());
        $res->assertRedirect('/request-recruitment/received');
        $req = RecruitmentRequest::first();
        $this->assertMatchesRegularExpression('/^REQ-\d{4}-[A-Z0-9]{6}$/', $req->reference);
        $this->assertSame('public', $req->source);
        $this->assertNull($req->subscription_id);
        $this->assertSame('new', $req->status);
        $this->assertSame($users, User::count());
        $this->assertSame(0, Organization::count());
        $this->assertSame(0, Subscription::count());
        $this->assertSame(['KE', 'UG'], $req->project_countries);
        $this->assertSame(['English', 'Swahili'], $req->languages);
    }

    public function test_validation_runs_server_side(): void
    {
        $this->post('/request-recruitment', ['org_name' => ''])->assertSessionHasErrors(['org_name', 'project_title', 'personnel_type', 'consent']);
        $this->assertSame(0, RecruitmentRequest::count());
    }

    public function test_requester_can_track_with_reference_and_email_only(): void
    {
        $this->post('/request-recruitment', $this->requestPayload());
        $ref = RecruitmentRequest::first()->reference;
        $this->get("/request-recruitment/track?reference=$ref&email=jane@acme.org")->assertSee('Household survey');
        $this->get("/request-recruitment/track?reference=$ref&email=other@x.org")->assertDontSee('Household survey');
    }
}
