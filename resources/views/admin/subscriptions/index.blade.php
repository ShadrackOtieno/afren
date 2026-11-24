@extends('layouts.admin')
@section('title', 'Subscriptions')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3"><h1 class="text-2xl font-bold">Subscriptions</h1>
<form method="POST" action="{{ route('admin.subscriptions.lifecycle') }}">@csrf<button class="rounded border bg-white px-4 py-2 text-sm" title="Issues renewal invoices and updates statuses (also runs daily)">Run renewal check now</button></form></div>
<form method="GET" class="mt-4 flex flex-wrap items-end gap-3"><x-field name="q" label="Organization" :value="request('q')"/><x-select name="status" label="Status" :options="['pending','active','past_due','cancelled','expired']" :value="request('status')" placeholder="Any"/><button class="rounded bg-brand-700 px-4 py-2 text-sm text-white">Filter</button></form>
<div class="mt-4 overflow-x-auto rounded-xl bg-white"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left"><tr><th class="p-3">Organization</th><th>Package</th><th>Status</th><th>Started</th><th>Renews</th><th></th></tr></thead><tbody class="divide-y">
@forelse($subs as $s)<tr><td class="p-3">{{ $s->organization->name }}<div class="text-xs text-slate-500">{{ $s->organization->email }}</div></td><td>{{ $s->package->name }}</td><td class="capitalize">{{ str_replace('_', ' ', $s->status) }}{{ $s->cancel_at_period_end ? ' (ending)' : '' }}</td><td>{{ $s->starts_at?->toDateString() ?? '—' }}</td><td>{{ $s->renews_at?->toDateString() ?? '—' }}</td><td><a class="text-brand-700 underline" href="{{ route('admin.subscriptions.show', $s) }}">Open</a></td></tr>
@empty<tr><td colspan="6" class="p-6 text-center text-slate-500">No subscriptions.</td></tr>@endforelse</tbody></table></div><div class="mt-4">{{ $subs->links() }}</div>
@endsection
