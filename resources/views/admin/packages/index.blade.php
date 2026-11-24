@extends('layouts.admin')
@section('title', 'Packages')
@section('content')
<div class="flex items-center justify-between"><h1 class="text-2xl font-bold">Subscription packages</h1><a href="{{ route('admin.packages.create') }}" class="rounded bg-brand-700 px-4 py-2 text-sm text-white">New package</a></div>
<div class="mt-4 overflow-x-auto rounded-xl bg-white"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left"><tr><th class="p-3">Name</th><th>Price</th><th>Requests/mo</th><th>Candidates/req</th><th>Active subs</th><th>Status</th><th></th></tr></thead><tbody class="divide-y">
@forelse($packages as $p)<tr><td class="p-3 font-medium">{{ $p->name }}</td><td>{{ number_format($p->price, 2) }} {{ $p->currency }}</td><td>{{ $p->max_requests_per_month ?? '—' }}</td><td>{{ $p->max_candidates_per_request ?? '—' }}</td><td>{{ $p->active_count }}</td><td>{{ $p->is_active ? 'Active' : 'Inactive' }}</td>
<td class="space-x-3 whitespace-nowrap"><a class="text-brand-700 underline" href="{{ route('admin.packages.edit', $p) }}">Edit</a>
<form class="inline" method="POST" action="{{ route('admin.packages.toggle', $p) }}">@csrf<button class="underline">{{ $p->is_active ? 'Deactivate' : 'Activate' }}</button></form></td></tr>
@empty<tr><td colspan="7" class="p-6 text-center text-slate-500">No packages yet. Create the first one — prices and limits are entirely up to you.</td></tr>@endforelse</tbody></table></div>
@endsection
