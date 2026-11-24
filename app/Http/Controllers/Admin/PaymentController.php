<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $r, Invoice $invoice, PaymentService $svc)
    {
        abort_unless(in_array($invoice->status, ['sent', 'draft']), 422, 'Only open invoices accept payments.');
        $d = $r->validate(['amount' => 'required|numeric|gt:0', 'method' => 'required|string|max:60', 'reference' => 'nullable|string|max:120', 'paid_on' => 'required|date|before_or_equal:today']);
        $svc->record($invoice, (float) $d['amount'], $d['method'], $d['reference'] ?? null, $d['paid_on']);
        return back()->with('status', 'Payment recorded.');
    }
}
