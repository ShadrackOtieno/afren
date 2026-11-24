@extends('layouts.admin')
@section('title', 'Messages')
@section('content')
<h1 class="text-2xl font-bold">Contact messages</h1>
<div class="mt-4 space-y-3">
@forelse($messages as $m)<article class="rounded-xl p-5 {{ $m->status === 'new' ? 'bg-accent-100' : 'bg-white' }}" x-data="{ o: {{ $m->status === 'new' ? 'true' : 'false' }} }">
<div class="flex flex-wrap items-start justify-between gap-2"><div><button class="font-semibold" @click="o = !o">{{ $m->subject }}</button><p class="text-xs text-slate-500">{{ $m->name }} &lt;{{ $m->email }}&gt; {{ $m->organization ? '· '.$m->organization : '' }} · {{ $m->created_at->diffForHumans() }}</p></div>
<form method="POST" action="{{ route('admin.messages.toggle', $m) }}">@csrf<button class="rounded border px-3 py-1 text-xs">{{ $m->status === 'new' ? 'Mark handled' : 'Reopen' }}</button></form></div>
<div x-show="o" x-cloak class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $m->message }}<p class="mt-3"><a class="text-brand-700 underline" href="mailto:{{ $m->email }}?subject=Re: {{ $m->subject }}">Reply by email</a></p></div></article>
@empty<p class="rounded-xl bg-white p-8 text-center text-slate-500">No messages yet.</p>@endforelse</div><div class="mt-4">{{ $messages->links() }}</div>
@endsection
