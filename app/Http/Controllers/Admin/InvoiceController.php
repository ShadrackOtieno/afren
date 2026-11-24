<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Invoice, RecruitmentRequest};
use App\Services\{Audit, InvoiceService};
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $svc) {}

    public function index(Request $r)
    {
        $q = Invoice::with(['payments'])->latest();
        $q->when($r->type, fn ($x, $v) => $x->where('type', $v));
        $q->when($r->status === 'overdue', fn ($x) => $x->where('status', 'sent')->whereDate('due_date', '<', now()), fn ($x) => $x->when($r->status, fn ($y, $v) => $y->where('status', $v)));
        $q->when($r->q, fn ($x, $v) => $x->where(fn ($w) => $w->where('number', 'like', "%$v%")->orWhere('bill_to_name', 'like', "%$v%")));
        return view('admin.invoices.index', ['invoices' => $q->paginate(25)->withQueryString()]);
    }

    /** Direct-service invoices are always prepared manually from a reviewed request: no automatic fixed pricing. */
    public function create(RecruitmentRequest $recruitmentRequest)
    {
        $tax = $this->svc->taxRateFor($recruitmentRequest->org_country);
        $invoice = new Invoice(['currency' => $recruitmentRequest->budget_currency ?: 'USD', 'tax_rate' => $tax, 'bill_to_name' => $recruitmentRequest->org_name, 'bill_to_email' => $recruitmentRequest->contact_email]);
        return view('admin.invoices.form', ['invoice' => $invoice, 'req' => $recruitmentRequest, 'items' => [['description' => '', 'quantity' => 1, 'unit_price' => '']]]);
    }

    public function store(Request $r, RecruitmentRequest $recruitmentRequest)
    {
        $d = $this->validated($r);
        $inv = $this->svc->create('direct', [
            'recruitment_request_id' => $recruitmentRequest->id, 'bill_to_name' => $d['bill_to_name'], 'bill_to_email' => $d['bill_to_email'], 'bill_to_country' => $recruitmentRequest->org_country,
            'currency' => strtoupper($d['currency']), 'due_date' => $d['due_date'] ?? null, 'notes' => $d['notes'] ?? null,
        ], $d['items'], (float) $d['tax_rate']);
        if ($recruitmentRequest->status === 'new') { $recruitmentRequest->update(['status' => 'under_review']); }
        Audit::log('invoice.created', $inv, ['request' => $recruitmentRequest->reference]);
        return redirect()->route('admin.invoices.show', $inv)->with('status', 'Draft invoice created. Review it, then send it to the client.');
    }

    public function show(Invoice $invoice) { return view('admin.invoices.show', ['i' => $invoice->load(['items', 'payments.recorder', 'request', 'subscription.organization'])]); }

    public function edit(Invoice $invoice)
    {
        abort_unless($invoice->status === 'draft' && $invoice->type === 'direct', 403, 'Only draft direct invoices can be edited.');
        return view('admin.invoices.form', ['invoice' => $invoice, 'req' => $invoice->request, 'items' => $invoice->items->map->only(['description', 'quantity', 'unit_price'])->all()]);
    }

    public function update(Request $r, Invoice $invoice)
    {
        abort_unless($invoice->status === 'draft' && $invoice->type === 'direct', 403);
        $d = $this->validated($r);
        $invoice->update(['bill_to_name' => $d['bill_to_name'], 'bill_to_email' => $d['bill_to_email'], 'currency' => strtoupper($d['currency']), 'due_date' => $d['due_date'] ?? null, 'notes' => $d['notes'] ?? null]);
        $this->svc->setItems($invoice, $d['items'], (float) $d['tax_rate']);
        Audit::log('invoice.updated', $invoice);
        return redirect()->route('admin.invoices.show', $invoice)->with('status', 'Invoice updated.');
    }

    public function send(Invoice $invoice)
    {
        abort_if($invoice->items()->count() === 0 || $invoice->total <= 0, 422, 'Add at least one priced line item first.');
        $this->svc->send($invoice);
        if ($invoice->type === 'direct' && in_array($invoice->request?->status, ['new', 'under_review'])) { $invoice->request->update(['status' => 'invoiced']); }
        Audit::log('invoice.sent', $invoice);
        return back()->with('status', 'Invoice sent to '.$invoice->bill_to_email.'.');
    }

    public function void(Invoice $invoice)
    {
        abort_if($invoice->payments()->exists(), 422, 'Invoices with payments cannot be voided.');
        $invoice->update(['status' => 'void']);
        Audit::log('invoice.voided', $invoice);
        return back()->with('status', 'Invoice voided.');
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'bill_to_name' => 'required|string|max:190', 'bill_to_email' => 'required|email', 'currency' => 'required|string|size:3',
            'tax_rate' => 'required|numeric|between:0,100', 'due_date' => 'nullable|date', 'notes' => 'nullable|string|max:3000',
            'items' => 'required|array|min:1', 'items.*.description' => 'required|string|max:255', 'items.*.quantity' => 'required|numeric|gt:0', 'items.*.unit_price' => 'required|numeric|min:0',
        ]);
    }
}
