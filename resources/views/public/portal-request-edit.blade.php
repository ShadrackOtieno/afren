@extends('layouts.public')
@section('title', 'Edit request '.$req->reference)
@section('content')
@include('partials.page-header', ['title' => 'Edit '.$req->reference, 'sub' => 'You can change a request until AFREN starts work on it. AFREN is notified of every change.'])
<div class="mx-auto max-w-4xl px-4 py-10"><a class="text-sm text-brand-700 underline" href="{{ route('portal.requests.show', $req->reference) }}">← Back to request</a>
    @include('partials.request-form', ['action' => route('portal.requests.update', $req->reference)])</div>
@endsection
