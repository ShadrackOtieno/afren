<?php
namespace App\Services;

use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Subscription;

class SubscriptionService
{
    public function __construct(private InvoiceService $invoices) {}

    /** New subscription: created as "pending" with a first invoice. Activates after payment confirmation. */
    public function subscribe(Organization $org, Package $pkg): Subscription
    {
        abort_unless($pkg->is_active, 422, 'This package is not available.');
        $sub = Subscription::create(['organization_id' => $org->id, 'package_id' => $pkg->id, 'status' => 'pending']);
        $this->event($sub, 'created', "Subscribed to {$pkg->name}");
        $this->issueInvoice($sub, $pkg, now(), now()->addMonth(), "{$pkg->name} – first month");
        return $sub;
    }

    public function issueInvoice(Subscription $sub, Package $pkg, $from, $to, string $desc, ?float $amount = null): Invoice
    {
        $org = $sub->organization;
        $inv = $this->invoices->create('subscription', [
            'organization_id' => $org->id, 'subscription_id' => $sub->id, 'bill_to_name' => $org->name, 'bill_to_email' => $org->email,
            'bill_to_country' => $org->country_code, 'currency' => $pkg->currency, 'period_start' => $from, 'period_end' => $to,
        ], [['description' => $desc, 'quantity' => 1, 'unit_price' => $amount ?? $pkg->price]]);
        $this->invoices->send($inv);
        return $inv;
    }

    public function onInvoicePaid(Subscription $sub, Invoice $inv): void
    {
        if ($sub->status === 'pending') {
            $sub->update(['status' => 'active', 'starts_at' => now(), 'renews_at' => now()->addMonth()]);
            $this->event($sub, 'activated', "Payment confirmed ({$inv->number})");
            Notifier::to($sub->organization->email, 'Subscription activated', "Your {$sub->package->name} subscription is active. Sign in to manage it: ".route('login'));
        } elseif (in_array($sub->status, ['active', 'past_due'], true) && $inv->period_end && $inv->period_end->greaterThan($sub->renews_at ?? now()->subDay())) {
            $update = ['status' => 'active', 'renews_at' => $inv->period_end];
            if ($sub->pending_package_id) { $update += ['package_id' => $sub->pending_package_id, 'pending_package_id' => null]; }
            $sub->update($update);
            $this->event($sub, 'renewed', "Renewed until {$inv->period_end->toDateString()}");
        }
    }

    /** Upgrade: effective immediately, prorated difference invoiced. Downgrade: applied at next renewal. */
    public function changePackage(Subscription $sub, Package $new): string
    {
        abort_unless($sub->isActive() && $new->is_active && $new->id !== $sub->package_id, 422, 'Cannot change to this package.');
        abort_unless($new->currency === $sub->package->currency, 422, 'Plan changes must use the same currency.');
        if ($new->price > $sub->package->price) {
            $days = max(0, now()->diffInDays($sub->renews_at, false));
            $diff = round(($new->price - $sub->package->price) * min(1, $days / 30), 2);
            $old = $sub->package->name;
            $sub->update(['package_id' => $new->id, 'pending_package_id' => null, 'cancel_at_period_end' => false]);
            $this->event($sub, 'upgraded', "{$old} → {$new->name}");
            if ($diff > 0) { $this->issueInvoice($sub, $new, now(), $sub->renews_at, "Upgrade to {$new->name} (prorated difference)", $diff); }
            return 'upgraded';
        }
        $sub->update(['pending_package_id' => $new->id]);
        $this->event($sub, 'downgrade_scheduled', "To {$new->name} at next renewal");
        return 'downgrade_scheduled';
    }

    public function cancel(Subscription $sub): void
    {
        if ($sub->status === 'pending') {
            $sub->update(['status' => 'cancelled', 'cancelled_at' => now(), 'ends_at' => now()]);
            $sub->invoices()->whereIn('status', ['sent', 'draft'])->update(['status' => 'void']);
        } else {
            $sub->update(['cancel_at_period_end' => true, 'cancelled_at' => now(), 'pending_package_id' => null]);
        }
        $this->event($sub, 'cancel_requested', $sub->status === 'pending' ? 'Cancelled before activation' : 'Ends at '.$sub->renews_at?->toDateString());
    }

    public function resume(Subscription $sub): void
    {
        abort_unless($sub->cancel_at_period_end && $sub->isActive(), 422);
        $sub->update(['cancel_at_period_end' => false, 'cancelled_at' => null]);
        $this->event($sub, 'resumed');
    }

    /** Daily lifecycle job: renewal invoices, grace period, expiry, end-of-term cancellation. Idempotent. */
    public function process(): array
    {
        $stats = ['invoiced' => 0, 'past_due' => 0, 'expired' => 0, 'cancelled' => 0];
        Subscription::with(['package', 'organization'])->whereIn('status', ['active', 'past_due'])->get()->each(function (Subscription $s) use (&$stats) {
            $due = $s->renews_at;
            if (! $due) { return; }
            if ($s->cancel_at_period_end && $due->isPast()) {
                $s->update(['status' => 'cancelled', 'ends_at' => $due]);
                $this->event($s, 'cancelled', 'Term ended'); $stats['cancelled']++; return;
            }
            if ($s->cancel_at_period_end) { return; }
            $hasOpen = $s->invoices()->where('status', 'sent')->where('period_end', '>', $due)->exists();
            if (! $hasOpen && $due->lte(now()->addDays(config('afren.renewal_notice_days')))) {
                $pkg = $s->pendingPackage ?? $s->package;
                $this->issueInvoice($s, $pkg, $due, $due->copy()->addMonth(), "{$pkg->name} – renewal"); $stats['invoiced']++;
            }
            if ($due->isPast() && $s->status === 'active') { $s->update(['status' => 'past_due']); $this->event($s, 'past_due'); $stats['past_due']++; }
            if ($due->copy()->addDays(config('afren.grace_days'))->isPast()) {
                $s->update(['status' => 'expired', 'ends_at' => now()]); $this->event($s, 'expired', 'Renewal not paid');
                Notifier::to($s->organization->email, 'Subscription expired', 'Your AFREN subscription expired because the renewal invoice was not paid.'); $stats['expired']++;
            }
        });
        return $stats;
    }

    public function event(Subscription $s, string $event, ?string $details = null): void
    {
        $s->events()->create(['event' => $event, 'details' => $details]);
    }
}
