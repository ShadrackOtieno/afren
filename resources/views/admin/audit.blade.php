@extends('layouts.admin')
@section('title', 'Audit log')
@section('content')
<h1 class="text-2xl font-bold">Audit log</h1>
<div class="mt-4 overflow-x-auto rounded-xl bg-white"><table class="min-w-full text-xs"><thead class="bg-slate-50 text-left"><tr><th class="p-3">When</th><th>User</th><th>Action</th><th>Subject</th><th>Details</th><th>IP</th></tr></thead><tbody class="divide-y">
@foreach($logs as $l)<tr><td class="p-2 whitespace-nowrap">{{ $l->created_at->toDateTimeString() }}</td><td>{{ $l->user?->name ?? 'system' }}</td><td>{{ $l->action }}</td><td>{{ $l->subject_type }} {{ $l->subject_id }}</td><td class="max-w-xs truncate">{{ $l->meta ? json_encode($l->meta) : '' }}</td><td>{{ $l->ip }}</td></tr>@endforeach</tbody></table></div><div class="mt-4">{{ $logs->links() }}</div>
@endsection
