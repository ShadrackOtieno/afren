@extends('layouts.admin')
@section('title', 'Invoices')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3"><h1 class="text-2xl font-bold">Invoices &amp; payments</h1><div class="space-x-3 text-sm"><a class="underline" href="{{ route('admin.reports.invoices', request()->only('type')) }}">Export invoices CSV</a><a class="underline" href="{{ route('admin.reports.payments') }}">Export payments CSV</a></div></div>
<form method="GET" class="mt-4 flex flex-wrap items-end gap-3"><x-field name="q" label="Number / client" :value="request('q')"/>
<x-select name="type" label="Type" :options="['subscription' => 'Subscription', 'direct' => 'Direct recruitment']" :value="request('type')" placeholder="Both"/>
<x-select name="status" label="Status" :options="['draft' => 'Draft', 'sent' => 'Sent', 'overdue' => 'Overdue', 'paid' => 'Paid', 'void' => 'Void']" :value="request('status')" placeholder="Any"/><button class="rounded bg-brand-700 px-4 py-2 text-sm text-white">Filter</button></form>
<div class="mt-4 overflow-x-auto rounded-xl bg-white"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left"><tr><th class="p-3">Number</th><th>Type</th><th>Client</th><th>Total</th><th>Balance</th><th>Due</th><th>Status</th></tr></thead><tbody class="divide-y">
@forelse($invoices as $i)<tr><td class="p-3"><a class="text-brand-700 underline" href="{{ route('admin.invoices.show', $i) }}">{{ $i->number }}</a></td><td>{{ $i->type === 'direct' ? 'Direct' : 'Subscription' }}</td><td>{{ $i->bill_to_name }}</td><td>{{ number_format($i->total, 2) }} {{ $i->currency }}</td><td>{{ number_format($i->balance(), 2) }}</td><td>{{ $i->due_date?->toDateString() ?? '—' }}</td>
<td class="capitalize">{{ $i->isOverdue() ? 'overdue' : $i->status }}</td></tr>@empty<tr><td colspan="7" class="p-6 text-center text-slate-500">No invoices.</td></tr>@endforelse</tbody></table></div><div class="mt-4">{{ $invoices->links() }}</div>
@endsection
