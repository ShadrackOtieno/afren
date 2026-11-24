@extends('layouts.public')
@section('title', 'Track a request')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
<h1 class="font-display text-4xl text-brand-900">Track your request</h1>
<form method="GET" class="mt-4 grid max-w-xl gap-4 rounded-xl border bg-white p-6">
    <x-field name="reference" label="Request reference" placeholder="REQ-2026-ABC123" :value="request('reference')" required/>
    <x-field name="email" label="Contact email used in the request" type="email" :value="request('email')" required/>
    <button class="rounded-md bg-brand-700 px-5 py-2 font-semibold text-white">Check status</button>
</form>
@if($found)
<div class="mt-6 max-w-xl rounded-xl border bg-white p-6"><p class="text-sm text-slate-500">{{ $found->reference }}</p><h2 class="text-xl font-bold">{{ $found->project_title }}</h2>
<p class="mt-2">Status: <strong>{{ config('afren.request_statuses')[$found->status] ?? $found->status }}</strong></p>
@foreach($found->invoices->whereIn('status', ['sent', 'paid']) as $i)<p class="mt-1 text-sm">Invoice {{ $i->number }}: {{ number_format($i->total, 2) }} {{ $i->currency }} — {{ $i->status }}</p>@endforeach</div>
@endif
</div>
@endsection
