@extends('layouts.public')
@section('title', 'Your subscription')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
<h1 class="font-display text-4xl text-brand-900">{{ $org->name }}</h1>
<p class="text-slate-600">Subscriber portal · signed in as {{ auth()->user()->email }}</p>
@include('partials.portal-nav', ['current' => 'overview'])
@forelse($subs as $s)
<section class="mt-6 rounded-xl border bg-white p-6" x-data="{ showPlans: false }">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div><h2 class="text-xl font-bold">{{ $s->package->name }} <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold uppercase">{{ str_replace('_', ' ', $s->status) }}</span></h2>
        <p class="text-sm text-slate-600">{{ number_format($s->package->price, 2) }} {{ $s->package->currency }} / {{ $s->package->billing_interval }}</p></div>
        <dl class="text-sm text-slate-600">
            <div>Started: {{ $s->starts_at?->toFormattedDateString() ?? 'pending payment' }}</div>
            <div>Renews: {{ $s->renews_at?->toFormattedDateString() ?? '—' }}</div>
            @if($s->pendingPackage)<div class="text-amber-700">Switching to {{ $s->pendingPackage->name }} at renewal</div>@endif
            @if($s->cancel_at_period_end)<div class="text-red-700">Cancels at end of period</div>@endif
        </dl>
    </div>
    @if($s->isActive())
        <p class="mt-3 text-sm">Requests this period: <strong>{{ $s->requestsThisPeriod() }}</strong> / {{ $s->package->max_requests_per_month ?? 'not capped' }}</p>
        <div class="mt-4 flex flex-wrap gap-3">
            <a class="rounded-md bg-brand-700 px-4 py-2 text-sm font-medium text-white" href="{{ route('request.create') }}">Submit a recruitment request</a>
            <button type="button" class="rounded-md border px-4 py-2 text-sm" @click="showPlans = !showPlans">Change plan</button>
            @if($s->cancel_at_period_end)
                <form method="POST" action="{{ route('portal.resume', $s->id) }}">@csrf<button class="rounded-md border px-4 py-2 text-sm">Keep my subscription</button></form>
            @else
                <form method="POST" action="{{ route('portal.cancel', $s->id) }}" onsubmit="return confirm('Cancel at the end of the paid period?')">@csrf<button class="rounded-md bg-red-50 px-4 py-2 text-sm text-red-700">Cancel subscription</button></form>
            @endif
        </div>
        <form x-show="showPlans" x-cloak method="POST" action="{{ route('portal.change', $s->id) }}" class="mt-4 flex flex-wrap items-end gap-3">@csrf
            <x-select name="package_id" label="New plan" :options="$packages->where('id', '!=', $s->package_id)->where('currency', $s->package->currency)->mapWithKeys(fn($p) => [$p->id => $p->name.' – '.number_format($p->price, 2).' '.$p->currency])"/>
            <button class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white">Apply</button>
            <p class="w-full text-xs text-slate-500">Upgrades apply immediately with a prorated invoice. Downgrades apply at your next renewal.</p>
        </form>
    @elseif($s->status === 'pending')
        <p class="mt-3 text-sm text-amber-700">Awaiting payment confirmation. Your plan activates as soon as AFREN confirms payment of the invoice below.</p>
        <form class="mt-3" method="POST" action="{{ route('portal.cancel', $s->id) }}">@csrf<button class="rounded-md border px-4 py-2 text-sm">Cancel</button></form>
    @endif
    <h3 class="mt-6 font-semibold">Billing history</h3>
    <div class="mt-2 overflow-x-auto"><table class="min-w-full text-sm"><thead class="text-left text-slate-500"><tr><th class="py-1 pr-4">Invoice</th><th class="pr-4">Total</th><th class="pr-4">Due</th><th>Status</th></tr></thead><tbody>
    @foreach($s->invoices->sortByDesc('id') as $i)<tr class="border-t"><td class="py-1 pr-4">{{ $i->number }}</td><td class="pr-4">{{ number_format($i->total, 2) }} {{ $i->currency }}</td><td class="pr-4">{{ $i->due_date?->toFormattedDateString() }}</td><td class="capitalize">{{ $i->status }}</td></tr>@endforeach</tbody></table></div>
    <h3 class="mt-4 font-semibold">History</h3>
    <ul class="mt-1 text-sm text-slate-600">@foreach($s->events as $e)<li>{{ $e->created_at->toDayDateTimeString() }} — {{ str_replace('_', ' ', $e->event) }} {{ $e->details ? '· '.$e->details : '' }}</li>@endforeach</ul>
</section>
@empty
<p class="mt-6">No subscriptions yet. <a class="text-brand-700 underline" href="{{ route('packages') }}">View packages</a>.</p>
@endforelse
</div>
@endsection
