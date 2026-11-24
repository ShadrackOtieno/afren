@extends('layouts.admin')
@section('title', 'Subscription')
@section('content')
<a class="text-sm text-brand-700 underline" href="{{ route('admin.subscriptions.index') }}">← Subscriptions</a>
<h1 class="mt-2 text-2xl font-bold">{{ $s->organization->name }} · {{ $s->package->name }} <span class="ml-2 rounded-full bg-slate-200 px-2 py-0.5 text-xs uppercase">{{ str_replace('_', ' ', $s->status) }}</span></h1>
<div class="mt-4 grid gap-6 lg:grid-cols-3">
<section class="space-y-1 rounded-xl bg-white p-5 text-sm"><p><b>Contact:</b> {{ $s->organization->contact_name }} · {{ $s->organization->email }}</p><p><b>Phone:</b> {{ $s->organization->dial_code }} {{ $s->organization->phone }}</p><p><b>Country:</b> {{ $s->organization->country_code }}</p>
<p><b>Price:</b> {{ number_format($s->package->price, 2) }} {{ $s->package->currency }}/mo</p><p><b>Started:</b> {{ $s->starts_at?->toDayDateTimeString() ?? '—' }}</p><p><b>Renews:</b> {{ $s->renews_at?->toDayDateTimeString() ?? '—' }}</p>
@if($s->pendingPackage)<p class="text-amber-700">Pending change to {{ $s->pendingPackage->name }}</p>@endif
<p><b>Requests this period:</b> {{ $s->requestsThisPeriod() }} / {{ $s->package->max_requests_per_month ?? '∞' }}</p>
@if(! in_array($s->status, ['cancelled', 'expired']))<form class="pt-3" method="POST" action="{{ route('admin.subscriptions.cancel', $s) }}" onsubmit="return confirm('Cancel immediately?')">@csrf<button class="rounded bg-red-50 px-3 py-1.5 text-red-700">Cancel immediately</button></form>@endif</section>
<section class="rounded-xl bg-white p-5 text-sm lg:col-span-2"><h2 class="font-semibold">Billing records</h2><table class="mt-2 w-full"><thead class="text-left text-slate-500"><tr><th>Invoice</th><th>Period</th><th>Total</th><th>Status</th></tr></thead><tbody>
@foreach($s->invoices->sortByDesc('id') as $i)<tr class="border-t"><td class="py-1"><a class="text-brand-700 underline" href="{{ route('admin.invoices.show', $i) }}">{{ $i->number }}</a></td><td>{{ $i->period_start?->toDateString() }} → {{ $i->period_end?->toDateString() }}</td><td>{{ number_format($i->total, 2) }} {{ $i->currency }}</td><td class="capitalize">{{ $i->status }}</td></tr>@endforeach</tbody></table>
<h2 class="mt-5 font-semibold">History</h2><ul class="mt-1 text-slate-600">@foreach($s->events as $e)<li>{{ $e->created_at->toDayDateTimeString() }} — {{ str_replace('_', ' ', $e->event) }} {{ $e->details ? '· '.$e->details : '' }}</li>@endforeach</ul>
<h2 class="mt-5 font-semibold">Requests under this subscription</h2><ul>@forelse($s->requests as $r)<li><a class="text-brand-700 underline" href="{{ route('admin.requests.show', $r) }}">{{ $r->reference }}</a> · {{ $r->project_title }}</li>@empty<li class="text-slate-500">None.</li>@endforelse</ul></section></div>
@endsection
