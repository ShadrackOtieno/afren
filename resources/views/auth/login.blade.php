@extends('layouts.public')
@section('title', 'Sign in')
@section('content')
<div class="mx-auto grid max-w-5xl items-center gap-10 px-4 py-14 md:grid-cols-2">
    <div><h1 class="font-display text-4xl text-brand-900">Sign in</h1>
        <p class="mt-3 text-slate-600">One sign-in for researchers, enumerators, subscribing organizations and AFREN staff.</p>
        <ul class="mt-6 space-y-3 text-sm text-slate-700">
            <li class="rounded-xl border border-slate-200 bg-white p-4"><b>Professional?</b> <a class="text-brand-700 underline" href="{{ route('register.create', 'researcher') }}">Register as a researcher</a> or <a class="text-brand-700 underline" href="{{ route('register.create', 'enumerator') }}">an enumerator</a>.</li>
            <li class="rounded-xl border border-slate-200 bg-white p-4"><b>Organization?</b> <a class="text-brand-700 underline" href="{{ route('packages') }}">Choose a monthly package</a> to get a portal. One-off requests need no account.</li></ul></div>
    <form method="POST" action="{{ route('login.post') }}" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-8">@csrf
        <x-field name="email" label="Email" type="email" required autofocus autocomplete="username"/>
        <x-field name="password" label="Password" type="password" required autocomplete="current-password"/>
        <div class="flex items-center justify-between text-sm"><label class="flex items-center gap-2"><input type="checkbox" name="remember"> Remember me</label><a class="text-brand-700 underline" href="{{ route('password.request') }}">Forgot password?</a></div>
        <button class="w-full rounded-md bg-brand-700 py-2.5 font-semibold text-white hover:bg-brand-800">Sign in</button></form>
</div>
@endsection
