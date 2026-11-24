@extends('layouts.public')
@section('title', 'Home')
@section('content')
{{-- Hero --}}
<section class="bg-brand-50 bg-dots-brand">
<div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-14 lg:grid-cols-2 lg:py-20">
    <div>
        <p class="inline-block rounded-full bg-accent-100 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-accent-700">Pan-African research workforce</p>
        <h1 class="mt-4 font-display text-4xl leading-tight text-brand-800 sm:text-5xl">Vetted researchers and enumerators, wherever your fieldwork is in Africa.</h1>
        <p class="mt-5 max-w-xl text-lg text-slate-600">AFREN maintains a verified, scored network of professionals and matches them to your project's exact requirements: location, languages, skills and tools.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('request.create') }}" class="rounded-md bg-accent-500 px-6 py-3 font-semibold text-white hover:bg-accent-600">Request Recruitment Support</a>
            <a href="{{ route('packages') }}" class="rounded-md bg-brand-100 px-6 py-3 font-semibold text-brand-800 hover:bg-brand-200">Explore Monthly Packages</a>
        </div>
        <p class="mt-4 text-sm text-slate-500">No account needed to submit a request. We review it and send you an invoice.</p>
    </div>
    <div class="relative"><div class="absolute -bottom-3 -right-3 h-full w-full rounded-3xl bg-accent-200" aria-hidden="true"></div>
        <img src="{{ \App\Support\Brand::svg('hero') }}" alt="Field researchers collecting survey data with a tablet" class="relative w-full rounded-3xl shadow-sm"></div>
</div></section>

{{-- Stats --}}
<section class="bg-brand-700 bg-dots-light text-white">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-9 text-sm sm:grid-cols-3">
        @foreach([['54','African countries supported, with local dialing codes and currencies'],['10','transparent scoring criteria, configurable by AFREN administrators'],['2','ways to engage: monthly subscription or one-off, invoice-based request']] as [$n,$t])
        <div class="flex items-start gap-4"><span class="font-display text-5xl text-accent-300">{{ $n }}</span><p class="pt-2 text-brand-100">{{ $t }}</p></div>@endforeach
    </div>
</section>

{{-- Two ways --}}
<section class="bg-white" aria-labelledby="ways"><div class="mx-auto max-w-7xl px-4 py-16">
    <h2 id="ways" class="font-display text-3xl text-brand-800">Two ways to work with AFREN</h2>
    <div class="mt-8 grid gap-6 md:grid-cols-2">
        <article class="flex flex-col rounded-2xl bg-brand-50 p-8">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-700 text-white"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12a8 8 0 0114-5.3M20 12a8 8 0 01-14 5.3M18 3v4h-4M6 21v-4h4"/></svg></span>
            <h3 class="mt-4 text-2xl font-bold text-brand-800">Monthly subscription</h3><p class="text-sm font-semibold text-brand-600">For ongoing needs</p>
            <p class="mt-3 flex-1 text-slate-600">Pick a package with defined benefits and limits, billed monthly. Manage your plan and track every request in your subscriber portal.</p>
            <a class="mt-6 font-semibold text-brand-700 hover:underline" href="{{ route('packages') }}">Compare packages →</a>
        </article>
        <article class="flex flex-col rounded-2xl bg-accent-50 p-8">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent-500 text-white"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h7l5 5v13H7zM14 3v5h5M10 13h6M10 17h6"/></svg></span>
            <h3 class="mt-4 text-2xl font-bold text-brand-800">Direct recruitment request</h3><p class="text-sm font-semibold text-accent-700">For one-off projects</p>
            <p class="mt-3 flex-1 text-slate-600">No account, no subscription. Complete a short step-by-step form, get a reference number, and receive an invoice for the agreed scope.</p>
            <a class="mt-6 font-semibold text-accent-700 hover:underline" href="{{ route('request.create') }}">Start a request →</a>
        </article>
    </div></div>
</section>

{{-- Professionals --}}
<section class="bg-accent-50 bg-dots-accent">
    <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-16 lg:grid-cols-2">
        <img src="{{ \App\Support\Brand::svg('professionals') }}" alt="A researcher and an enumerator at work" class="w-full rounded-3xl" loading="lazy">
        <div>
            <h2 class="font-display text-3xl text-brand-800">Researcher or enumerator? Join the network.</h2>
            <ul class="mt-5 space-y-3 text-slate-700">
                @foreach(['Free registration. Register as a researcher, an enumerator, or both.','A transparent profile score shows exactly how to improve.','Sign in any time to update your profile, CV and availability.'] as $li)
                <li class="flex gap-3"><span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent-500 text-xs text-white">✓</span>{{ $li }}</li>@endforeach
            </ul>
            <div class="mt-7 flex flex-wrap gap-3"><a class="rounded-md bg-brand-700 px-5 py-3 font-semibold text-white hover:bg-brand-800" href="{{ route('register.create', 'researcher') }}">Register as a Researcher</a>
            <a class="rounded-md bg-brand-100 px-5 py-3 font-semibold text-brand-800 hover:bg-brand-200" href="{{ route('register.create', 'enumerator') }}">Register as an Enumerator</a></div>
        </div>
    </div>
</section>

{{-- Process --}}
<section class="bg-white"><div class="mx-auto max-w-7xl px-4 py-16">
    <h2 class="font-display text-3xl text-brand-800">How a request moves</h2>
    <ol class="mt-8 grid gap-6 md:grid-cols-4">
        @foreach([['Submit','Tell us about your organization, project and the people you need.'],['Review','AFREN reviews the scope and confirms the arrangement.'],['Invoice','You receive an invoice based on the requested service.'],['Match','After payment, we match, shortlist and coordinate.']] as $i => [$t,$d])
        <li class="rounded-xl p-6 {{ $i % 2 ? 'bg-accent-50' : 'bg-brand-50' }}"><span class="flex h-9 w-9 items-center justify-center rounded-full font-bold text-white {{ $i % 2 ? 'bg-accent-500' : 'bg-brand-700' }}">{{ $i + 1 }}</span><h3 class="mt-4 font-semibold text-brand-800">{{ $t }}</h3><p class="mt-2 text-sm text-slate-600">{{ $d }}</p></li>@endforeach
    </ol></div>
</section>

{{-- FAQ --}}
<section class="bg-slate-100" x-data="{ o: 1 }"><div class="mx-auto max-w-4xl px-4 py-16">
    <h2 class="font-display text-3xl text-brand-800">Common questions</h2>
    <div class="mt-6 divide-y divide-slate-200 rounded-xl bg-white shadow-sm">
        @foreach([['Do I need an account to request recruitment?','No. The direct request form needs no account, login, subscription or upfront payment.'],['Do I have to subscribe after submitting a request?','Never. A request never signs you up for a subscription.'],['How are professionals ranked?','Each profile gets a transparent completeness score, and every request is matched against its own requirements to produce a separate job-fit score.'],['Can subscribers track their requests?','Yes. The subscriber portal shows request status, shortlisted candidates and billing history.']] as $i => [$q,$a])
        <div><button class="flex w-full items-center justify-between px-5 py-4 text-left font-medium" @click="o = o === {{ $i + 1 }} ? 0 : {{ $i + 1 }}" :aria-expanded="o === {{ $i + 1 }}">{{ $q }}<span x-text="o === {{ $i + 1 }} ? '−' : '+'" class="text-xl text-accent-600"></span></button>
        <p x-show="o === {{ $i + 1 }}" x-cloak class="px-5 pb-4 text-slate-600">{{ $a }}</p></div>@endforeach
    </div></div>
</section>

{{-- Call to action --}}
<section class="bg-brand-800 bg-dots-light text-white"><div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-6 px-4 py-12">
    <div><h2 class="font-display text-3xl">Ready to find the right people for your fieldwork?</h2><p class="mt-2 text-brand-100">Send a request in five short steps. No account needed.</p></div>
    <a href="{{ route('request.create') }}" class="rounded-md bg-accent-500 px-7 py-3 font-semibold text-white hover:bg-accent-600">Request Recruitment Support</a>
</div></section>
@endsection
