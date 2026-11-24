@extends('layouts.public')
@section('title', 'Request Recruitment Support')
@section('content')
@include('partials.page-header', ['title' => 'Request recruitment support', 'sub' => 'Five short steps. No account, login, subscription or upfront payment. AFREN reviews your request and sends an invoice for the agreed scope.'])
<div class="mx-auto max-w-4xl px-4 py-10">
    @if($org)<div class="rounded-lg bg-brand-50 p-4 text-sm text-brand-900">Submitting as subscriber <b>{{ $org->name }}</b>: this request counts toward your package limits. <a class="underline" href="{{ route('request.create', ['direct' => 1]) }}">Submit as a one-off direct request instead</a> · <a class="underline" href="{{ route('portal.requests') }}">Back to portal</a></div>
    @elseif(request()->boolean('direct') && auth()->check())<div class="rounded-lg border border-slate-200 bg-slate-100 p-4 text-sm">This is a direct request. It is invoiced separately and is not part of any subscription.</div>@endif
    @include('partials.request-form', ['req' => null, 'action' => route('request.store')])
</div>
@endsection
