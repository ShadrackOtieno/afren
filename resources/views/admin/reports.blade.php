@extends('layouts.admin')
@section('title', 'Financial reports')
@section('content')
<h1 class="text-2xl font-bold">Financial reports</h1>
<form method="GET" class="mt-4 flex flex-wrap items-end gap-3"><x-field name="from" label="From (issue date)" type="date" :value="$from->toDateString()"/><x-field name="to" label="To" type="date" :value="$to->toDateString()"/><button class="rounded bg-brand-700 px-4 py-2 text-sm text-white">Apply</button>
<a class="text-sm underline" href="{{ route('admin.reports.invoices') }}">Invoices CSV</a><a class="text-sm underline" href="{{ route('admin.reports.payments') }}">Payments CSV</a></form>
<div class="mt-4 overflow-x-auto rounded-xl bg-white"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left"><tr><th class="p-3">Revenue stream</th><th>Currency</th><th>Invoices</th><th>Invoiced</th><th>Collected</th><th>Outstanding</th></tr></thead><tbody class="divide-y">
@forelse($rows as $r)<tr><td class="p-3">{{ $r['type'] === 'direct' ? 'Direct recruitment' : 'Subscriptions' }}</td><td>{{ $r['currency'] }}</td><td>{{ $r['count'] }}</td><td>{{ number_format($r['invoiced'], 2) }}</td><td>{{ number_format($r['paid'], 2) }}</td><td>{{ number_format($r['outstanding'], 2) }}</td></tr>@empty<tr><td colspan="6" class="p-6 text-center text-slate-500">No issued invoices in this period.</td></tr>@endforelse</tbody></table></div>
<p class="mt-2 text-xs text-slate-500">Currencies are reported separately; no automatic conversion is applied.</p>
@endsection
