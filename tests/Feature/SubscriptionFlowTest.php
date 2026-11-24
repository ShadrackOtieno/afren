<?php
namespace Tests\Feature;

use App\Models\{Invoice, Organization, RecruitmentRequest, Subscription};
use App\Services\{PaymentService, SubscriptionService};

class SubscriptionFlowTest extends AfrenTestCase
{
    private function subscribe(array $pkg = []): Subscription
    {
        $p = $this->package($pkg);
        $this->post("/packages/{$p->slug}/subscribe", $this->subscribePayload())->assertRedirect('/portal');
        return Subscription::firstOrFail();
    }

    private function pay(Invoice $i): void
    {
        $this->actingAs($this->staff('finance'));
        app(PaymentService::class)->record($i, (float) $i->total, 'Bank transfer', 'TX1', now()->toDateString());
    }

    public function test_subscription_is_pending_until_payment_is_confirmed(): void
    {
        $sub = $this->subscribe();
        $this->assertSame('pending', $sub->status);
        $inv = $sub->invoices()->first();
        $this->assertSame('subscription', $inv->type);
        $this->assertSame('sent', $inv->status);
        $this->assertStringStartsWith('SUB-', $inv->number);
        $this->pay($inv);
        $sub->refresh();
        $this->assertSame('active', $sub->status);
        $this->assertNotNull($sub->renews_at);
    }

    public function test_renewal_invoice_extends_period_and_unpaid_renewal_expires(): void
    {
        $sub = $this->subscribe(); $this->pay($sub->invoices()->first());
        $sub->update(['renews_at' => now()->addDays(3)]);
        app(SubscriptionService::class)->process();
        $this->assertSame(2, $sub->invoices()->count());
        app(SubscriptionService::class)->process(); // idempotent
        $this->assertSame(2, $sub->invoices()->count());
        $renewal = $sub->invoices()->latest('id')->first();
        $old = $sub->fresh()->renews_at;
        $this->pay($renewal);
        $this->assertTrue($sub->fresh()->renews_at->greaterThan($old));

        $sub->update(['renews_at' => now()->subDays(20), 'status' => 'active']);
        $sub->invoices()->latest('id')->first()->update(['period_end' => now()->subDays(5)]);
        $sub->invoices()->update(['status' => 'void']);
        app(SubscriptionService::class)->process();
        $this->assertSame('expired', $sub->fresh()->status);
    }

    public function test_upgrade_downgrade_cancel_from_the_signed_in_portal(): void
    {
        $sub = $this->subscribe(['price' => 100]); $this->pay($sub->invoices()->first());
        $big = $this->package(['name' => 'Big', 'slug' => 'big', 'price' => 300]);
        $small = $this->package(['name' => 'Small', 'slug' => 'small', 'price' => 50]);
        $this->actingAs($sub->organization->user);
        $this->post("/portal/subscriptions/{$sub->id}/change", ['package_id' => $big->id])->assertRedirect();
        $this->assertSame($big->id, $sub->fresh()->package_id);
        $this->assertSame(2, $sub->invoices()->count()); // prorated difference invoice
        $this->post("/portal/subscriptions/{$sub->id}/change", ['package_id' => $small->id])->assertRedirect();
        $this->assertSame($small->id, $sub->fresh()->pending_package_id);
        $this->post("/portal/subscriptions/{$sub->id}/cancel")->assertRedirect();
        $this->assertTrue($sub->fresh()->cancel_at_period_end);
        $this->assertSame('active', $sub->fresh()->status);
    }

    public function test_package_limits_apply_to_signed_in_subscriber_requests_only(): void
    {
        $org = $this->subscriber(['max_requests_per_month' => 1]);
        $this->actingAs($org->user);
        $this->get('/request-recruitment')->assertSee('counts toward your package limits');
        $this->post('/request-recruitment', $this->requestPayload())->assertRedirect('/request-recruitment/received');
        $this->post('/request-recruitment', $this->requestPayload())->assertSessionHasErrors('subscription');
        // The same organisation can still send a one-off direct request (invoiced separately)
        $this->post('/request-recruitment', $this->requestPayload(['direct' => 1]))->assertRedirect('/request-recruitment/received');
        $this->assertSame(2, RecruitmentRequest::count());
        $this->assertSame(1, RecruitmentRequest::where('source', 'subscriber')->count());
    }

    public function test_existing_email_must_sign_in_instead_of_creating_a_second_account(): void
    {
        $p = $this->package();
        $this->post("/packages/{$p->slug}/subscribe", $this->subscribePayload())->assertRedirect('/portal');
        auth()->logout();
        $this->post("/packages/{$p->slug}/subscribe", $this->subscribePayload())->assertSessionHasErrors('email');
    }

    public function test_inactive_package_cannot_be_purchased(): void
    {
        $p = $this->package(['is_active' => false]);
        $this->get("/packages/{$p->slug}/subscribe")->assertNotFound();
    }
}
