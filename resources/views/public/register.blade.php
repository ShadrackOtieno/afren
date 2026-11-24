@extends('layouts.public')
@section('title', 'Register as a '.ucfirst($type))
@section('content')
@include('partials.page-header', ['title' => 'Register as '.($type === 'researcher' ? 'a Researcher' : 'an Enumerator'), 'sub' => 'Free for professionals. You can register as a researcher, an enumerator, or both. Create a login to keep your profile up to date.'])
<div class="mx-auto max-w-4xl px-4 py-10">@include('partials.professional-form', ['action' => route('register.store')])</div>
@endsection
