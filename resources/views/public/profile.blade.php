@extends('layouts.public')
@section('title', 'My profile')
@section('content')
@include('partials.page-header', ['title' => 'My profile', 'sub' => $pro->full_name.' · '.$pro->reference])
<div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 lg:grid-cols-3">
<aside class="space-y-6 lg:order-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-6"><p class="text-sm text-slate-500">Profile score</p><p class="font-display text-5xl text-brand-800">{{ $pro->profile_score }}<span class="text-lg text-slate-400"> / 100</span></p>
        <p class="mt-2 text-sm"><span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $pro->verification_status === 'verified' ? 'bg-brand-100 text-brand-800' : ($pro->verification_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ ucfirst($pro->verification_status) }}</span>
        <span class="text-slate-500">{{ $pro->verification_status === 'pending' ? 'Awaiting AFREN review' : '' }}</span></p>
        <ul class="mt-5 space-y-3 text-sm">@foreach($pro->score_breakdown ?? [] as $b)<li><div class="flex justify-between"><span>{{ $b['label'] }}</span><span class="text-slate-500">{{ $b['points'] }}/{{ $b['max_points'] }}</span></div><div class="mt-1 h-1.5 rounded bg-slate-200"><div class="h-1.5 rounded {{ $b['fraction'] >= 1 ? 'bg-brand-600' : 'bg-accent-500' }}" style="width: {{ $b['fraction'] * 100 }}%"></div></div></li>@endforeach</ul>
        <p class="mt-4 text-xs text-slate-500">Bars in orange can still be improved. Verification is done by AFREN staff.</p></section>
    <section class="space-y-2 rounded-2xl border border-slate-200 bg-white p-6 text-sm"><h2 class="font-semibold text-brand-900">Documents</h2>
        <p><a class="text-brand-700 underline" href="{{ route('profile.cv') }}">Download my CV</a></p>
        @foreach($pro->documents ?? [] as $i => $d)<p class="flex justify-between">Supporting document {{ $i + 1 }} <button form="del{{ $i }}" class="text-red-600 underline">Remove</button></p>@endforeach</section>
</aside>
<div class="lg:col-span-2">@include('partials.professional-form', ['action' => route('profile.update'), 'type' => $pro->category])
@foreach($pro->documents ?? [] as $i => $d)<form id="del{{ $i }}" method="POST" action="{{ route('profile.documents.destroy', $i) }}">@csrf @method('DELETE')</form>@endforeach</div>
</div>
@endsection
