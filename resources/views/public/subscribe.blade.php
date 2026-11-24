@extends('layouts.public')
@section('title', 'Subscribe to '.$package->name)
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
<h1 class="font-display text-4xl text-brand-900">Subscribe: {{ $package->name }}</h1>
<p class="mt-1 text-slate-600">{{ number_format($package->price, 2) }} {{ $package->currency }} per {{ $package->billing_interval }}. An invoice is emailed after you submit; the plan activates once payment is confirmed.</p>
@if($org)
<form method="POST" action="{{ route('packages.store', $package->slug) }}" class="mt-6 max-w-xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6">@csrf
    @include('partials.flash')
    <p class="text-sm">Subscribing as <b>{{ $org->name }}</b> ({{ $org->email }}).</p>
    <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="terms" value="1" class="mt-1" required> <span>I accept the <a class="underline" href="{{ route('terms') }}" target="_blank">Terms</a> and <a class="underline" href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>.</span></label>
    <button class="rounded-md bg-brand-700 px-5 py-2.5 font-semibold text-white hover:bg-brand-800">Subscribe &amp; receive invoice</button>
</form>
@else
@php
    $map = ['name' => 1, 'type' => 1, 'country_code' => 1, 'location' => 1, 'website' => 1, 'contact_name' => 2, 'email' => 2, 'dial_code' => 2, 'phone' => 2, 'password' => 2];
    $startStep = $errors->any() ? min(array_map(fn ($k) => $map[explode('.', $k)[0]] ?? 3, $errors->keys())) : 1;
    $card = 'rounded-2xl border border-slate-200 bg-white p-6 sm:p-8';
@endphp
<form method="POST" action="{{ route('packages.store', $package->slug) }}" x-ref="form" novalidate class="mt-8 max-w-3xl"
      x-data="wizard({{ $startStep }}, ['Organization', 'Contact & login', 'Review'], false)"
      @keydown.enter="if ($event.target.tagName !== 'TEXTAREA' && $event.target.type !== 'submit') { $event.preventDefault(); if (step < total) next(); }" @submit="submitGuard($event)">
    @csrf <input type="text" name="hp" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
    <div x-ref="top" class="scroll-mt-24"></div>
    @include('partials.flash')
    <p class="mb-4 text-sm text-slate-600">Already have an account? <a class="text-brand-700 underline" href="{{ route('login') }}">Sign in</a> first.</p>
    @include('partials.wizard-stepper')
    <section x-ref="s1" data-step="1" x-show="step === 1" class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-800">Organization</h2><p class="mt-1 text-sm text-slate-500">Tell us who is subscribing.</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <x-field name="name" label="Organization name" required class="sm:col-span-2"/>
            <x-select name="type" label="Organization type" :options="config('afren.org_types')" required/>
            <x-select name="country_code" label="Country" :options="$countries->pluck('name', 'iso2')" required/>
            <x-field name="location" label="City / location"/><x-field name="website" label="Website" type="url" placeholder="https://"/></div></section>
    <section x-ref="s2" data-step="2" x-show="step === 2" x-cloak class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-800">Contact person &amp; login</h2><p class="mt-1 text-sm text-slate-500">Your email and password give you access to the subscriber portal.</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <x-field name="contact_name" label="Contact person" required/><x-field name="email" label="Email address (your login)" type="email" required/>
            <x-select name="dial_code" label="Dialing code" :options="$countries->mapWithKeys(fn($c) => [$c->dial_code => $c->name.' ('.$c->dial_code.')'])" required/>
            <x-field name="phone" label="Phone number" type="tel" required/></div>
        <div class="mt-4">@include('partials.password-fields')</div></section>
    <section x-ref="s3" data-step="3" x-show="step === 3" x-cloak class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-800">Review &amp; subscribe</h2><p class="mt-1 text-sm text-slate-500">{{ $package->name }}: {{ number_format($package->price, 2) }} {{ $package->currency }} per {{ $package->billing_interval }}. An invoice is emailed after you submit.</p>
        @include('partials.wizard-review')
        <label class="mt-6 flex items-start gap-2 text-sm"><input type="checkbox" name="terms" value="1" class="mt-1" required> <span>I accept the <a class="underline" href="{{ route('terms') }}" target="_blank">Terms</a> and <a class="underline" href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>.</span></label></section>
    @include('partials.wizard-buttons', ['submit' => 'Create account & receive invoice'])
</form>
@endif
</div>
@endsection
