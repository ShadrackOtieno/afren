@extends('layouts.public')
@section('title', 'How It Works')
@section('content')
@include('partials.page-header', ['title' => 'How it works', 'sub' => 'Three audiences, one coordinated process.'])
<section class="bg-brand-50 bg-dots-brand"><div class="mx-auto grid max-w-7xl gap-6 px-4 py-14 md:grid-cols-3">
@foreach([
 ['Monthly subscription', 'emerald', ['Compare packages and choose one.','Enter your organization details and receive the first invoice.','Your plan activates when AFREN confirms payment.','Submit and track requests in your subscriber portal. Upgrade, downgrade or cancel any time.']],
 ['Direct request', 'clay', ['Open the request form: no account or login.','Complete five short steps: organization, project, personnel, commercial, review.','Receive a confirmation and a unique reference.','AFREN reviews and sends an invoice; after payment we match and shortlist.']],
 ['For professionals', 'emerald', ['Register as a researcher, an enumerator or both.','Upload your CV and documents; AFREN verifies your profile.','Your score is calculated automatically with a clear breakdown.','Sign in any time to update your profile.']]] as [$t, $c, $steps])
<section class="rounded-2xl p-7 shadow-sm {{ $c === 'clay' ? 'bg-accent-50' : 'bg-white' }}"><h2 class="text-xl font-bold {{ $c === 'clay' ? 'text-accent-600' : 'text-brand-800' }}">{{ $t }}</h2>
<ol class="mt-4 space-y-4">@foreach($steps as $i => $s)<li class="flex gap-3 text-sm text-slate-700"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $c === 'clay' ? 'bg-accent-500' : 'bg-brand-700' }} text-xs font-bold text-white">{{ $i + 1 }}</span>{{ $s }}</li>@endforeach</ol></section>@endforeach
</div></section>
@endsection
