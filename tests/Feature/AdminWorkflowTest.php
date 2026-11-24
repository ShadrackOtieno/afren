<?php
namespace Tests\Feature;

use App\Models\{Invoice, RecruitmentRequest, Subscription};

class AdminWorkflowTest extends AfrenTestCase
{
    public function test_admin_area_requires_authentication_and_roles(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->actingAs($this->staff('recruiter'))->get('/admin/invoices')->assertForbidden();
        $this->actingAs($this->staff('finance'))->get('/admin/professionals')->assertForbidden();
        $this->actingAs($this->staff('admin'))->get('/admin/users')->assertForbidden();
        $this->actingAs($this->staff('super_admin'))->get('/admin/users')->assertOk();
    }

    public function test_direct_request_to_invoice_to_payment_without_any_subscription(): void
    {
        $this->post('/request-recruitment', $this->requestPayload());
        $req = RecruitmentRequest::first();
        $admin = $this->actingAs($this->staff('admin'));
        $admin->get("/admin/requests/{$req->id}")->assertOk()->assertSee('Direct request');

        // no auto-pricing: nothing exists until an admin prepares an invoice
        $this->assertSame(0, Invoice::count());
        $admin->post("/admin/requests/{$req->id}/invoice", [
            'bill_to_name' => 'Acme Research', 'bill_to_email' => 'jane@acme.org', 'currency' => 'usd', 'tax_rate' => 16,
            'items' => [['description' => 'Recruit 10 enumerators', 'quantity' => 10, 'unit_price' => 50]],
        ])->assertRedirect();
        $inv = Invoice::first();
        $this->assertSame('direct', $inv->type);
        $this->assertEquals(500, $inv->subtotal);
        $this->assertEquals(580, $inv->total);
        $this->assertSame('draft', $inv->status);
        $this->assertStringStartsWith('DIR-', $inv->number);

        $admin->post("/admin/invoices/{$inv->id}/send")->assertRedirect();
        $this->assertSame('invoiced', $req->fresh()->status);
        $this->assertSame('sent', $inv->fresh()->status);

        $admin->post("/admin/invoices/{$inv->id}/payments", ['amount' => 200, 'method' => 'Bank', 'paid_on' => now()->toDateString()])->assertRedirect();
        $this->assertSame('sent', $inv->fresh()->status);
        $this->assertSame(380.0, $inv->fresh()->balance());
        $admin->post("/admin/invoices/{$inv->id}/payments", ['amount' => 380, 'method' => 'Bank', 'paid_on' => now()->toDateString()])->assertRedirect();
        $this->assertSame('paid', $inv->fresh()->status);
        $this->assertSame('paid', $req->fresh()->status);
        $admin->post("/admin/invoices/{$inv->id}/payments", ['amount' => 1, 'method' => 'x', 'paid_on' => now()->toDateString()])->assertStatus(422);
        $this->assertSame(0, Subscription::count());
    }

    public function test_both_models_work_side_by_side_and_are_reported_separately(): void
    {
        $pkg = $this->package(['price' => 100]);
        $this->post("/packages/{$pkg->slug}/subscribe", $this->subscribePayload(['email' => 'a@s.org']));
        auth()->logout();
        $this->post('/request-recruitment', $this->requestPayload(['contact_email' => 'direct@x.org']));
        $admin = $this->actingAs($this->staff('admin'));
        $sub = Subscription::first(); $subInv = $sub->invoices()->first();
        $admin->post("/admin/invoices/{$subInv->id}/payments", ['amount' => 100, 'method' => 'Card', 'paid_on' => now()->toDateString()]);
        $this->assertSame('active', $sub->fresh()->status);
        $req = RecruitmentRequest::first();
        $this->assertSame('new', $req->status);
        $this->assertNull($req->subscription_id);
        $admin->get('/admin/invoices?type=subscription')->assertSee($subInv->number);
        $admin->get('/admin/reports')->assertOk()->assertSee('Subscriptions');
        $admin->get('/admin/reports/invoices.csv')->assertOk();
    }

    public function test_shortlist_respects_package_candidate_cap(): void
    {
        $org = $this->subscriber(['max_candidates_per_request' => 1]); $sub = $org->subscriptions()->first();
        $this->actingAs($org->user)->post('/request-recruitment', $this->requestPayload());
        $req = RecruitmentRequest::first(); $this->assertSame($sub->id, $req->subscription_id);
        $a = $this->pro(); $b = $this->pro();
        $admin = $this->actingAs($this->staff('recruiter'));
        $admin->post("/admin/requests/{$req->id}/shortlist", ['professional_id' => $a->id])->assertSessionHasNoErrors();
        $admin->post("/admin/requests/{$req->id}/shortlist", ['professional_id' => $b->id])->assertSessionHasErrors('shortlist');
        $this->assertSame(1, $req->shortlists()->count());
    }

    public function test_packages_are_admin_controlled(): void
    {
        $admin = $this->actingAs($this->staff('admin'));
        $admin->post('/admin/packages', ['name' => 'Custom', 'price' => 12.5, 'currency' => 'ngn', 'features' => "One\nTwo", 'is_active' => 1])->assertRedirect();
        $p = \App\Models\Package::where('name', 'Custom')->first();
        $this->assertSame('NGN', $p->currency); $this->assertSame(['One', 'Two'], $p->features); $this->assertNull($p->max_requests_per_month);
        $this->get('/packages')->assertSee('Custom');
        $admin->post("/admin/packages/{$p->id}/toggle");
        $this->get('/packages')->assertDontSee('Custom');
    }

    public function test_private_documents_are_not_public(): void
    {
        $p = $this->pro();
        $this->get("/admin/professionals/{$p->id}/cv")->assertRedirect('/admin/login');
    }
}
