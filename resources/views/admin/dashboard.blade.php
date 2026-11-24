@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<h1 class="text-2xl font-bold">Dashboard</h1>
<div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
@foreach([['Professionals', $pros, 'admin.professionals.index'], ['Awaiting verification', $prosPending, 'admin.professionals.index'], ['New requests', $newRequests, 'admin.requests.index'], ['Open requests', $openRequests, 'admin.requests.index'],
          ['Active subscriptions', $activeSubs, 'admin.subscriptions.index'], ['Subscriptions pending payment', $pendingSubs, 'admin.subscriptions.index'], ['Unpaid invoices', $unpaid, 'admin.invoices.index'], ['Overdue invoices', $overdue, 'admin.invoices.index']] as [$l, $n, $r])
<a href="{{ route($r) }}" class="rounded-xl p-5 shadow-sm hover:shadow-md {{ $loop->index % 4 === 2 || $loop->index === 7 ? 'bg-accent-50' : 'bg-brand-50' }}"><p class="text-sm text-slate-500">{{ $l }}</p><p class="text-3xl font-bold">{{ $n }}</p></a>
@endforeach</div>
<div class="mt-8 grid gap-6 lg:grid-cols-2">
<section class="rounded-xl bg-white p-5"><h2 class="font-semibold">Latest requests</h2><ul class="mt-2 divide-y text-sm">@forelse($latestRequests as $r)<li class="py-2"><a class="text-brand-700 hover:underline" href="{{ route('admin.requests.show', $r) }}">{{ $r->reference }}</a> · {{ $r->org_name }} — {{ $r->project_title }} <span class="text-xs text-slate-500">({{ $r->source }})</span></li>@empty<li class="py-2 text-slate-500">None yet.</li>@endforelse</ul></section>
<section class="rounded-xl bg-white p-5"><h2 class="font-semibold">Latest registrations</h2><ul class="mt-2 divide-y text-sm">@forelse($latestPros as $p)<li class="py-2"><a class="text-brand-700 hover:underline" href="{{ route('admin.professionals.show', $p) }}">{{ $p->full_name }}</a> · {{ $p->category }} · {{ $p->country_code }} · score {{ $p->profile_score }}</li>@empty<li class="py-2 text-slate-500">None yet.</li>@endforelse</ul></section></div>
@endsection
