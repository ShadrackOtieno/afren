@extends('layouts.admin')
@section('title', 'Staff users')
@section('content')
<h1 class="text-2xl font-bold">Staff users</h1>
<div class="mt-4 grid gap-6 lg:grid-cols-2"><div class="rounded-xl bg-white p-5"><table class="w-full text-sm"><tbody class="divide-y">@foreach($users as $u)<tr><td class="py-2">{{ $u->name }}<div class="text-xs text-slate-500">{{ $u->email }}</div></td><td>{{ $u->role }}</td><td>{{ $u->is_active ? 'Active' : 'Disabled' }}</td>
<td>@if($u->id !== auth()->id())<form method="POST" action="{{ route('admin.users.toggle', $u) }}">@csrf<button class="underline">{{ $u->is_active ? 'Disable' : 'Enable' }}</button></form>@endif</td></tr>@endforeach</tbody></table></div>
<form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3 rounded-xl bg-white p-5">@csrf<h2 class="font-semibold">Add staff</h2><x-field name="name" label="Name" required/><x-field name="email" label="Email" type="email" required/>
<x-select name="role" label="Role" :options="['admin' => 'Admin (full operations)', 'recruiter' => 'Recruiter (professionals & requests)', 'finance' => 'Finance (subscriptions, invoices, payments)', 'super_admin' => 'Super admin']" :placeholder="null" required/>
<x-field name="password" label="Password (min 10, mixed case + number)" type="password" required autocomplete="new-password"/><button class="rounded bg-brand-700 px-4 py-2 text-sm text-white">Create</button></form></div>
@endsection
