@extends('layouts.public')
@section('title', 'My requests')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
<h1 class="font-display text-4xl text-brand-900">{{ $org->name }}</h1>
@include('partials.portal-nav', ['current' => 'requests'])
@if(! $active)<div class="mt-6 rounded-lg bg-amber-50 p-4 text-sm text-amber-900">You have no active subscription, so new requests under the portal are paused. Existing requests remain visible below.</div>@endif
<div class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white"><table class="min-w-full text-sm"><thead class="bg-slate-100 text-left text-slate-600"><tr><th class="p-4">Reference</th><th>Project</th><th>Need</th><th>Shortlisted</th><th>Status</th><th>Submitted</th></tr></thead><tbody class="divide-y divide-slate-100">
@forelse($requests as $r)<tr class="hover:bg-slate-50"><td class="p-4"><a class="font-semibold text-brand-700 underline" href="{{ route('portal.requests.show', $r->reference) }}">{{ $r->reference }}</a></td><td>{{ \Illuminate\Support\Str::limit($r->project_title, 45) }}</td><td>{{ $r->headcount }} {{ $r->personnel_type }}</td><td>{{ $r->shortlists->count() }}</td><td>@include('partials.status-pill', ['status' => $r->status])</td><td>{{ $r->created_at->toFormattedDateString() }}</td></tr>
@empty<tr><td colspan="6" class="p-10 text-center text-slate-500">No requests yet. <a class="text-brand-700 underline" href="{{ route('request.create') }}">Submit your first request</a>.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $requests->links() }}</div></div>
@endsection
