<?php
namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use App\Models\TaxRule;

class InvoiceService
{
    /** Country-specific rule, else default (country null) rule, else 0. Fully admin-configured. */
    public function taxRateFor(?string $country): float
    {
        $rules = TaxRule::where('is_active', true)->get();
        $r = $rules->firstWhere('country_code', $country) ?? $rules->firstWhere('country_code', null);
        return (float) ($r->rate ?? 0);
    }

    /** @param array<int,array{description:string,quantity:float|int|string,unit_price:float|int|string}> $items */
    public function create(string $type, array $attrs, array $items, ?float $taxRate = null): Invoice
    {
        $taxRate ??= $this->taxRateFor($attrs['bill_to_country'] ?? null);
        $inv = Invoice::create($attrs + ['type' => $type, 'tax_rate' => $taxRate, 'status' => 'draft']);
        $this->setItems($inv, $items, $taxRate);
        $prefix = $type === 'subscription' ? 'SUB' : 'DIR';
        $inv->update(['number' => sprintf('%s-%s-%05d', $prefix, now()->format('Y'), $inv->id)]);
        return $inv->fresh();
    }

    public function setItems(Invoice $inv, array $items, float $taxRate): void
    {
        $inv->items()->delete();
        $sub = 0;
        foreach ($items as $i) {
            if (trim((string) ($i['description'] ?? '')) === '') { continue; }
            $qty = (float) $i['quantity']; $price = (float) $i['unit_price']; $amt = round($qty * $price, 2); $sub += $amt;
            $inv->items()->create(['description' => $i['description'], 'quantity' => $qty, 'unit_price' => $price, 'amount' => $amt]);
        }
        $tax = round($sub * $taxRate / 100, 2);
        $inv->update(['subtotal' => $sub, 'tax_rate' => $taxRate, 'tax_total' => $tax, 'total' => $sub + $tax]);
    }

    /** Issue (send) an invoice to the nominated contact. */
    public function send(Invoice $inv): Invoice
    {
        abort_if(in_array($inv->status, ['paid', 'void']), 422, 'Invoice is already '.$inv->status);
        $inv->update(['status' => 'sent', 'issue_date' => $inv->issue_date ?? now(), 'due_date' => $inv->due_date ?? now()->addDays(config('afren.invoice_due_days')), 'sent_at' => now()]);
        $lines = $inv->items->map(fn ($i) => "- {$i->description}: {$i->quantity} x {$i->unit_price}")->implode("\n");
        Notifier::to($inv->bill_to_email, "Invoice {$inv->number}", "Dear {$inv->bill_to_name},\n\nPlease find your AFREN invoice {$inv->number}.\n\n{$lines}\n\nSubtotal: {$inv->subtotal} {$inv->currency}\nTax ({$inv->tax_rate}%): {$inv->tax_total}\nTotal due: {$inv->total} {$inv->currency}\nDue date: {$inv->due_date->toDateString()}\n\n".(Setting::get('payment_instructions') ?: 'Our team will share payment instructions with you.')."\n\nThank you,\nAFREN");
        return $inv;
    }
}
