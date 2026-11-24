@extends('layouts.admin')
@section('title', 'Recruitment requests')
@section('content')
<h1 class="text-2xl font-bold">Recruitment requests</h1>
<form method="GET" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5"><x-field name="q" label="Search" :value="request('q')"/>
<x-select name="status" label="Status" :options="config('afren.request_statuses')" :value="request('status')" placeholder="Any"/>
<x-select name="source" label="Source" :options="['public' => 'Direct (public form)', 'subscriber' => 'Subscriber']" :value="request('source')" placeholder="Any"/>
<x-select name="assigned_to" label="Assigned to" :options="$staff->pluck('name', 'id')" :value="request('assigned_to')" placeholder="Anyone"/>
<div class="flex items-end"><button class="rounded bg-brand-700 px-4 py-2 text-sm text-white">Filter</button></div></form>
<div class="mt-4 overflow-x-auto rounded-xl bg-white"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left"><tr><th class="p-3">Reference</th><th>Organization</th><th>Project</th><th>Need</th><th>Source</th><th>Status</th><th>Assigned</th><th>Received</th></tr></thead><tbody class="divide-y">
@forelse($requests as $r)<tr><td class="p-3"><a class="text-brand-700 underline" href="{{ route('admin.requests.show', $r) }}">{{ $r->reference }}</a></td><td>{{ $r->org_name }}</td><td>{{ \Illuminate\Support\Str::limit($r->project_title, 40) }}</td><td>{{ $r->headcount }} {{ $r->personnel_type }}</td><td>{{ $r->source === 'public' ? 'Direct' : 'Subscriber' }}</td><td>{{ config('afren.request_statuses')[$r->status] ?? $r->status }}</td><td>{{ $r->assignee?->name ?? '—' }}</td><td>{{ $r->created_at->toDateString() }}</td></tr>
@empty<tr><td colspan="8" class="p-6 text-center text-slate-500">No requests.</td></tr>@endforelse</tbody></table></div><div class="mt-4">{{ $requests->links() }}</div>
@endsection
