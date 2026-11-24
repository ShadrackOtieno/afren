@extends('layouts.public')
@section('title', 'Forgot password')
@section('content')
<div class="mx-auto max-w-md px-4 py-14"><h1 class="font-display text-3xl text-brand-900">Reset your password</h1>
<form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4 rounded-2xl border border-slate-200 bg-white p-8">@csrf
<p class="text-sm text-slate-600">Enter your account email and we will send you a reset link.</p><x-field name="email" label="Email" type="email" required/>
<button class="w-full rounded-md bg-brand-700 py-2.5 font-semibold text-white">Send reset link</button><a class="block text-center text-sm underline" href="{{ route('login') }}">Back to sign in</a></form></div>
@endsection
