@extends('layouts.public')
@section('title', 'Contact Us')
@section('content')
@include('partials.page-header', ['title' => 'Contact us', 'sub' => 'Questions about registration, packages or an existing request? Send us a message.'])
<section class="bg-slate-100"><div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 lg:grid-cols-3">
    <form method="POST" action="{{ route('contact.store') }}" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-7 lg:col-span-2">@csrf
        <input type="text" name="hp" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
        <div class="grid gap-4 sm:grid-cols-2"><x-field name="name" label="Your name" required/><x-field name="email" label="Email address" type="email" required/>
        <x-field name="organization" label="Organization (optional)"/><x-field name="subject" label="Subject" required/></div>
        <x-textarea name="message" label="Message" rows="6" required hint="If this is about an existing request, include its reference (e.g. REQ-2026-ABC123)."/>
        <button class="rounded-md bg-brand-700 px-6 py-3 font-semibold text-white hover:bg-brand-800">Send message</button>
    </form>
    <aside class="space-y-5 text-sm text-slate-700">
        <div class="rounded-2xl bg-brand-50 p-6"><h2 class="font-semibold text-brand-900">Email &amp; phone</h2>@php($site = \App\Support\Site::all())<p class="mt-1"><a class="underline" href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a>@if($site['phone'])<br><a class="underline" href="{{ \App\Support\Site::telHref($site['phone']) }}">{{ $site['phone'] }}</a>@endif</p></div>
        <div class="rounded-2xl bg-accent-50 p-6"><h2 class="font-semibold text-brand-900">Already submitted a request?</h2><p class="mt-1"><a class="underline" href="{{ route('request.track') }}">Track it with your reference</a></p></div>
        <div class="rounded-2xl bg-white p-6"><h2 class="font-semibold text-brand-900">Professional or subscriber?</h2><p class="mt-1"><a class="underline" href="{{ route('login') }}">Sign in</a> to manage your profile or portal</p></div>
    </aside>
</div></section>
@endsection
