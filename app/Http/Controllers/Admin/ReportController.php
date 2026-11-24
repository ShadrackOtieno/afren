<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Invoice, Payment};
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $r)
    {
        $from = $r->date('from') ?? now()->startOfYear(); $to = $r->date('to') ?? now();
        $invoices = Invoice::with('payments')->whereNotIn('status', ['draft', 'void'])->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])->get();
        $rows = $invoices->groupBy(fn ($i) => $i->type.'|'.$i->currency)->map(fn ($g, $k) => [
            'type' => explode('|', $k)[0], 'currency' => explode('|', $k)[1], 'count' => $g->count(), 'invoiced' => $g->sum('total'),
            'paid' => $g->sum(fn ($i) => $i->paidAmount()), 'outstanding' => $g->sum(fn ($i) => $i->balance()),
        ])->values();
        return view('admin.reports', ['rows' => $rows, 'from' => $from, 'to' => $to]);
    }

    public function exportInvoices(Request $r): StreamedResponse
    {
        return $this->csv('afren-invoices.csv', ['Number', 'Type', 'Bill to', 'Email', 'Currency', 'Subtotal', 'Tax', 'Total', 'Paid', 'Balance', 'Status', 'Issue date', 'Due date'],
            Invoice::with('payments')->when($r->type, fn ($q, $v) => $q->where('type', $v))->orderBy('id')->cursor()->map(fn ($i) => [
                $i->number, $i->type, $i->bill_to_name, $i->bill_to_email, $i->currency, $i->subtotal, $i->tax_total, $i->total, $i->paidAmount(), $i->balance(), $i->status, $i->issue_date?->toDateString(), $i->due_date?->toDateString()]));
    }

    public function exportPayments(): StreamedResponse
    {
        return $this->csv('afren-payments.csv', ['Date', 'Invoice', 'Type', 'Amount', 'Currency', 'Method', 'Reference'],
            Payment::with('invoice')->orderBy('paid_on')->cursor()->map(fn ($p) => [$p->paid_on->toDateString(), $p->invoice->number, $p->invoice->type, $p->amount, $p->invoice->currency, $p->method, $p->reference]));
    }

    private function csv(string $name, array $head, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($head, $rows) {
            $o = fopen('php://output', 'w'); fputcsv($o, $head);
            foreach ($rows as $row) { fputcsv($o, array_map(fn ($c) => is_string($c) && preg_match('/^[=+\-@]/', $c) ? "'".$c : $c, $row)); }
            fclose($o);
        }, $name, ['Content-Type' => 'text/csv']);
    }
}
