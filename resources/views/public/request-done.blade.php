@extends('layouts.public')
@section('title', 'Request received')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
<div class="mx-auto max-w-xl rounded-xl border bg-white p-8 text-center">
<h1 class="text-2xl font-bold">Request received</h1>
@if($reference)<p class="mt-3">Your unique request reference:</p><p class="mt-1 text-2xl font-mono font-bold text-brand-800">{{ $reference }}</p><p class="mt-2 text-sm text-slate-500">Please keep this reference. A confirmation has also been emailed.</p>@endif
<p class="mt-4 text-slate-600">AFREN will review your requirements and send an invoice based on the scope. You do not need an account or a subscription.</p>
<a href="{{ route('request.track') }}" class="mt-6 inline-block text-brand-700 underline">Track this request</a></div>
</div>
@endsection
