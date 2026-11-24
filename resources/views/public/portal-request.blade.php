@extends('layouts.public')
@section('title', $r->reference)
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
<a class="text-sm text-brand-700 underline" href="{{ route('portal.requests') }}">← All requests</a>
<div class="mt-2 flex flex-wrap items-start justify-between gap-3"><div><p class="text-sm text-slate-500">{{ $r->reference }} · submitted {{ $r->created_at->toFormattedDateString() }}</p><h1 class="font-display text-3xl text-brand-900">{{ $r->project_title }}</h1></div>
@if(in_array($r->status, ['new', 'under_review']))<a href="{{ route('portal.requests.edit', $r->reference) }}" class="rounded-md bg-brand-100 px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-200">Edit request</a>@endif</div>

@php($order = ['new', 'under_review', 'invoiced', 'paid', 'shortlisting', 'completed'] )
@if($r->status === 'declined')<p class="mt-6 rounded-lg bg-red-50 p-4 text-sm text-red-800">This request was declined. Please contact AFREN for details.</p>
@else<ol class="mt-6 grid grid-cols-3 gap-2 text-xs font-medium sm:grid-cols-6">@foreach($order as $i => $st)@php($idx = array_search($r->status, $order))<li class="rounded-lg px-2 py-2 text-center {{ $i < $idx ? 'bg-brand-100 text-brand-800' : ($i === $idx ? 'bg-brand-700 text-white' : 'bg-slate-100 text-slate-500') }}">{{ config('afren.request_statuses')[$st] }}</li>@endforeach</ol>@endif

<div class="mt-8 grid gap-6 lg:grid-cols-3">
<div class="space-y-6 lg:col-span-2">
<section class="rounded-2xl border border-slate-200 bg-white p-6"><h2 class="font-semibold text-brand-900">Shortlisted candidates</h2>
@forelse($r->shortlists as $s)@php($p = $s->professional)<div class="mt-3 flex flex-wrap items-start justify-between gap-3 border-t border-slate-100 pt-3 text-sm">
<div><p class="font-semibold">{{ $p->full_name }}</p><p class="text-slate-500">{{ $p->category }} · {{ $p->country?->name }} · {{ $p->years_experience }} yrs · {{ implode(', ', $p->languages ?? []) }}</p>
@if(in_array($s->status, ['accepted', 'hired']))<p class="mt-1 text-slate-600">{{ $p->email }} · {{ $p->dial_code }} {{ $p->phone }}</p>@endif</div>
<div class="text-right"><span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold capitalize">{{ $s->status }}</span><p class="mt-1 text-xs text-slate-500">Match {{ $s->job_score }}/100</p></div></div>
@empty<p class="mt-2 text-sm text-slate-500">No candidates yet. AFREN will shortlist after review{{ $r->source === 'subscriber' ? '' : ' and payment' }}.</p>@endforelse
<p class="mt-4 text-xs text-slate-500">Contact details are shared once a candidate accepts. Needed: {{ $r->headcount }}; hired so far: {{ $r->shortlists->where('status', 'hired')->count() }}.</p></section>
<section class="space-y-1 rounded-2xl border border-slate-200 bg-white p-6 text-sm"><h2 class="font-semibold text-brand-900">Request summary</h2>
<p><b>Sector:</b> {{ $r->sector }} · <b>Countries:</b> {{ implode(', ', $r->project_countries) }}</p><p><b>Need:</b> {{ $r->headcount }} {{ $r->personnel_type }} · min {{ ucfirst($r->min_education) }}, {{ $r->min_years }}+ yrs</p>
<p><b>Skills:</b> {{ implode(', ', $r->technical_skills ?? []) ?: '—' }} · <b>Languages:</b> {{ implode(', ', $r->languages ?? []) ?: '—' }} · <b>Tools:</b> {{ implode(', ', $r->tools ?? []) ?: '—' }}</p>
<p class="whitespace-pre-line pt-2 text-slate-600">{{ $r->project_description }}</p></section></div>
<aside class="space-y-6">
<section class="rounded-2xl border border-slate-200 bg-white p-6 text-sm"><h2 class="font-semibold text-brand-900">Package</h2><p class="mt-1">{{ $r->subscription?->package?->name }}</p>
<p class="text-slate-500">Max shortlisted per request: {{ $r->subscription?->package?->max_candidates_per_request ?? 'not capped' }}</p></section>
<section class="rounded-2xl border border-slate-200 bg-white p-6 text-sm"><h2 class="font-semibold text-brand-900">Invoices</h2>
@forelse($r->invoices->whereIn('status', ['sent', 'paid']) as $i)<p class="mt-2">{{ $i->number }} · {{ number_format($i->total, 2) }} {{ $i->currency }} · <span class="capitalize">{{ $i->status }}</span></p>@empty<p class="mt-1 text-slate-500">None. This request is covered by your subscription.</p>@endforelse</section>
</aside></div></div>
@endsection
