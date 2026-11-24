<?php
namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/** Only admins record payments; invoice/subscription/request state follows from the recorded payments. */
class PaymentService
{
    public function __construct(private SubscriptionService $subs) {}

    public function record(Invoice $inv, float $amount, string $method, ?string $reference, string $paidOn): Payment
    {
        abort_if($inv->status === 'void', 422, 'Cannot pay a void invoice.');
        abort_if($amount <= 0 || $amount > $inv->balance() + 0.001, 422, 'Amount must be positive and not exceed the balance.');

        return DB::transaction(function () use ($inv, $amount, $method, $reference, $paidOn) {
            $p = $inv->payments()->create(['amount' => $amount, 'method' => $method, 'reference' => $reference, 'paid_on' => $paidOn, 'recorded_by' => auth()->id()]);
            Audit::log('payment.recorded', $inv, ['amount' => $amount, 'method' => $method, 'ref' => $reference]);
            if ($inv->fresh()->balance() <= 0) {
                $inv->update(['status' => 'paid']);
                $this->onPaid($inv->fresh());
            }
            return $p;
        });
    }

    private function onPaid(Invoice $inv): void
    {
        if ($inv->type === 'subscription' && $inv->subscription) {
            $this->subs->onInvoicePaid($inv->subscription, $inv);
        }
        if ($inv->type === 'direct' && $inv->request && in_array($inv->request->status, ['new', 'under_review', 'invoiced'])) {
            $inv->request->update(['status' => 'paid']);
            Notifier::to($inv->bill_to_email, "Payment received for {$inv->request->reference}", "We have confirmed payment for invoice {$inv->number}. AFREN will now proceed with your recruitment.");
        }
    }
}
