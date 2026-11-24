@extends('layouts.public')
@section('title', 'Choose a new password')
@section('content')
<div class="mx-auto max-w-md px-4 py-14"><h1 class="font-display text-3xl text-brand-900">Choose a new password</h1>
<form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4 rounded-2xl border border-slate-200 bg-white p-8">@csrf <input type="hidden" name="token" value="{{ $token }}">
<x-field name="email" label="Email" type="email" :value="$email" required/>
<x-field name="password" label="New password" type="password" required hint="At least 8 characters with upper and lower case letters and a number." autocomplete="new-password"/>
<x-field name="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password"/>
<button class="w-full rounded-md bg-brand-700 py-2.5 font-semibold text-white">Update password</button></form></div>
@endsection
