@extends('layouts.public')
@section('title', 'About AFREN')
@section('content')
@include('partials.page-header', ['title' => 'About AFREN', 'sub' => 'A centrally managed network that makes finding qualified research talent in Africa faster and more transparent.'])
<section class="bg-white"><div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 lg:grid-cols-2">
    <div class="space-y-4 text-slate-700">
        <p>The Africa Researcher &amp; Enumerator Network (AFREN) connects organizations with qualified researchers and enumerators across the continent. AFREN verifies each profile, scores it against configurable criteria, and matches professionals to the specific requirements of every project.</p>
        <p>Organizations choose how they work with us: a monthly subscription for ongoing access, or a direct request for a single engagement. AFREN administrators manage the professional database, recruitment coordination, invoicing and payments centrally.</p>
    </div>
    <div class="relative"><div class="absolute -bottom-3 -right-3 h-full w-full rounded-3xl bg-brand-100" aria-hidden="true"></div><img src="{{ \App\Support\Brand::svg('organisations') }}" alt="An organization reviewing a shortlist" class="relative w-full rounded-3xl"></div>
</div></section>
<section class="bg-brand-50 bg-dots-brand"><div class="mx-auto max-w-7xl px-4 py-14"><h2 class="font-display text-3xl text-brand-800">What guides us</h2>
<div class="mt-8 grid gap-6 md:grid-cols-3">
@foreach([['Continental reach','Registration, billing and matching work across African countries, languages and currencies.','bg-white'],['Transparency','Professionals see how their score is built; clients see why candidates were shortlisted.','bg-accent-50'],['Human oversight','Every shortlist is reviewed by AFREN staff before it reaches you.','bg-white']] as [$t,$d,$c])
<div class="rounded-xl p-6 shadow-sm {{ $c }}"><h3 class="font-semibold text-brand-800">{{ $t }}</h3><p class="mt-2 text-sm text-slate-600">{{ $d }}</p></div>@endforeach</div></div></section>
@endsection
