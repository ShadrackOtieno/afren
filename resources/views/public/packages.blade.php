@extends('layouts.public')
@section('title', 'Monthly Subscription Packages')
@section('content')
@include('partials.page-header', ['title' => 'Monthly subscription packages', 'sub' => 'For organizations with ongoing or recurring needs. Each package has its own price, benefits and usage limits.'])
<section class="bg-brand-50 bg-dots-brand"><div class="mx-auto max-w-7xl px-4 py-12">
<p class="max-w-3xl text-slate-600">Packages suit organizations with ongoing or recurring needs. Each package has its own price, benefits and usage limits, billed monthly. Payment is confirmed by AFREN before your plan activates.</p>
<p class="mt-2 text-sm text-slate-600">Prefer not to subscribe? <a class="font-semibold text-brand-700 underline" href="{{ route('request.create') }}">Submit a direct recruitment request</a> — no account needed; we'll review it and send an invoice.</p>

@if($packages->isEmpty())
    <p class="mt-8 rounded-lg border bg-white p-6">Subscription packages will be announced soon. You can still <a class="text-brand-700 underline" href="{{ route('request.create') }}">request recruitment support</a>.</p>
@else
<div class="mt-8 grid gap-6 md:grid-cols-{{ min(3, max(1, $packages->count())) }}">
    @foreach($packages as $p)
    <section class="flex flex-col rounded-xl p-6 {{ $p->is_featured ? 'bg-brand-100 shadow-lg' : 'border border-slate-200 bg-white' }}" aria-labelledby="pk{{ $p->id }}">
        @if($p->is_featured)<span class="mb-2 w-fit rounded-full bg-accent-500 px-2 py-0.5 text-xs font-semibold text-white">Most popular</span>@endif
        <h2 id="pk{{ $p->id }}" class="text-xl font-bold">{{ $p->name }}</h2>
        <p class="text-sm text-slate-500">{{ $p->tagline }}</p>
        <p class="mt-4"><span class="text-3xl font-extrabold">{{ number_format($p->price, 2) }}</span> <span class="text-sm text-slate-500">{{ $p->currency }} / {{ $p->billing_interval }}</span></p>
        <ul class="mt-4 flex-1 space-y-1 text-sm text-slate-700">@foreach($p->features ?? [] as $f)<li>✓ {{ $f }}</li>@endforeach</ul>
        <a class="mt-6 rounded-md bg-brand-700 px-4 py-2 text-center font-medium text-white hover:bg-brand-800" href="{{ route('packages.subscribe', $p->slug) }}">Choose {{ $p->name }}</a>
    </section>
    @endforeach
</div>

<h2 class="mt-12 text-xl font-bold">Compare packages</h2>
<div class="mt-3 overflow-x-auto rounded-lg border bg-white">
<table class="min-w-full text-sm"><caption class="sr-only">Package comparison</caption>
<thead class="bg-brand-700 text-left text-white"><tr><th scope="col" class="p-3">Feature</th>@foreach($packages as $p)<th scope="col" class="p-3">{{ $p->name }}</th>@endforeach</tr></thead>
<tbody class="divide-y">
<tr><th scope="row" class="p-3 text-left font-medium">Price</th>@foreach($packages as $p)<td class="p-3">{{ number_format($p->price, 2) }} {{ $p->currency }}</td>@endforeach</tr>
<tr><th scope="row" class="p-3 text-left font-medium">Billing</th>@foreach($packages as $p)<td class="p-3 capitalize">{{ $p->billing_interval }}</td>@endforeach</tr>
<tr><th scope="row" class="p-3 text-left font-medium">Requests per month</th>@foreach($packages as $p)<td class="p-3">{{ $p->max_requests_per_month ?? 'As agreed' }}</td>@endforeach</tr>
<tr><th scope="row" class="p-3 text-left font-medium">Shortlisted candidates / request</th>@foreach($packages as $p)<td class="p-3">{{ $p->max_candidates_per_request ?? 'As agreed' }}</td>@endforeach</tr>
</tbody></table></div>
@endif
<p class="mt-6 text-xs text-slate-500">Taxes are applied on invoices according to your country where applicable. Already subscribed? Use the manage link from your confirmation email.</p>
</div></section>
@endsection
