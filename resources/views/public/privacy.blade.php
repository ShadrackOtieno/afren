@extends('layouts.public')
@section('title', 'Privacy Policy')
@section('content')
<div class="mx-auto max-w-3xl space-y-4 px-4 py-12 text-slate-700"><h1 class="font-display text-4xl text-brand-900">Privacy Policy</h1>
<p class="rounded bg-amber-50 p-3 text-sm text-amber-900">Template text — have this reviewed by legal counsel and adapted to the data-protection laws of the countries where you operate (e.g. Kenya DPA 2019, Nigeria NDPA 2023, South Africa POPIA, GDPR where applicable).</p>
<h2 class="pt-2 text-xl font-semibold text-brand-900">What we collect</h2><p>Professionals: identity and contact details, qualifications, CV, experience, skills, languages and availability. Organizations: contact details, project information and uploaded briefs.</p>
<h2 class="pt-2 text-xl font-semibold text-brand-900">How we use it</h2><p>To verify and score profiles, match professionals to recruitment requests, share shortlisted profiles with the requesting organization, issue invoices and administer subscriptions.</p>
<h2 class="pt-2 text-xl font-semibold text-brand-900">Sharing &amp; retention</h2><p>Professional details are shared only with organizations for whom you are shortlisted. CVs and documents are stored privately and accessible only to authorized AFREN staff. You may request correction or deletion at any time.</p>
<h2 class="pt-2 text-xl font-semibold text-brand-900">Contact</h2><p>{{ config('mail.from.address') }}</p></div>
@endsection
