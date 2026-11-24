<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AFREN') · Africa Researcher & Enumerator Network</title>
    <meta name="description" content="@yield('description', 'AFREN connects organizations with vetted researchers and enumerators across Africa.')">
    @include('partials.head')
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-3">Skip to content</a>
@include('partials.topbar')
<header class="sticky top-0 z-40 bg-brand-700 text-white shadow-md" x-data="{ open: false }" @keydown.escape="open = false">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white"><img src="{{ \App\Support\Brand::svg('favicon') }}" alt="" class="h-7 w-7"></span><span class="text-lg font-bold tracking-tight">AFREN</span></a>
        <nav class="hidden items-center gap-6 text-sm font-medium xl:flex" aria-label="Main">
            <a class="hover:text-accent-200" href="{{ route('about') }}">About</a>
            <a class="hover:text-accent-200" href="{{ route('how') }}">How it works</a>
            <a class="hover:text-accent-200" href="{{ route('packages') }}">Monthly packages</a>
            <div class="relative" x-data="{ m: false }" @click.outside="m = false">
                <button class="flex items-center gap-1 hover:text-accent-200" @click="m = !m" :aria-expanded="m">For professionals <span aria-hidden="true">▾</span></button>
                <div x-show="m" x-cloak class="absolute right-0 mt-3 w-56 rounded-lg bg-white py-2 text-slate-800 shadow-xl">
                    <a class="block px-4 py-2 hover:bg-brand-50" href="{{ route('register.create', 'researcher') }}">Register as a Researcher</a>
                    <a class="block px-4 py-2 hover:bg-brand-50" href="{{ route('register.create', 'enumerator') }}">Register as an Enumerator</a></div></div>
            <div class="relative" x-data="{ m: false }" @click.outside="m = false">
                <button class="flex items-center gap-1 hover:text-accent-200" @click="m = !m" :aria-expanded="m">For organizations <span aria-hidden="true">▾</span></button>
                <div x-show="m" x-cloak class="absolute right-0 mt-3 w-60 rounded-lg bg-white py-2 text-slate-800 shadow-xl">
                    <a class="block px-4 py-2 hover:bg-brand-50" href="{{ route('request.create') }}">Request recruitment support</a>
                    <a class="block px-4 py-2 hover:bg-brand-50" href="{{ route('request.track') }}">Track a request</a></div></div>
            <a class="hover:text-accent-200" href="{{ route('contact') }}">Contact</a>
            @auth
            <div class="relative" x-data="{ m: false }" @click.outside="m = false"><button class="flex items-center gap-1 rounded-md bg-white/10 px-3 py-1.5 hover:bg-white/20" @click="m = !m" :aria-expanded="m">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 18) }} <span aria-hidden="true">▾</span></button>
                <div x-show="m" x-cloak class="absolute right-0 mt-3 w-52 rounded-lg bg-white py-2 text-slate-800 shadow-xl"><a class="block px-4 py-2 hover:bg-brand-50" href="{{ auth()->user()->homeRoute() }}">{{ auth()->user()->isStaff() ? 'Admin dashboard' : (auth()->user()->role === 'professional' ? 'My profile' : 'My portal') }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="block w-full px-4 py-2 text-left hover:bg-brand-50">Sign out</button></form></div></div>
            @else
            <a class="rounded-md bg-white/10 px-3 py-1.5 hover:bg-white/20" href="{{ route('login') }}">Sign in</a>
            @endauth
            <a class="rounded-md bg-accent-500 px-4 py-2 font-semibold text-white hover:bg-accent-600" href="{{ route('request.create') }}">Request Recruitment Support</a>
        </nav>
        <button class="rounded p-2 xl:hidden" @click="open = !open" :aria-expanded="open" aria-label="Toggle menu"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
    </div>
    <nav x-show="open" x-cloak class="border-t border-white/10 bg-white px-4 py-4 text-sm text-slate-800 xl:hidden" aria-label="Mobile">
        <div class="grid gap-1">
            @foreach([['About AFREN','about'],['How it works','how'],['Monthly packages','packages'],['Contact us','contact']] as [$l,$r])<a class="rounded px-2 py-2 hover:bg-brand-50" href="{{ route($r) }}">{{ $l }}</a>@endforeach
            <p class="mt-2 px-2 text-xs font-semibold uppercase text-slate-500">Professionals</p>
            <a class="rounded px-2 py-2 hover:bg-brand-50" href="{{ route('register.create', 'researcher') }}">Register as a Researcher</a>
            <a class="rounded px-2 py-2 hover:bg-brand-50" href="{{ route('register.create', 'enumerator') }}">Register as an Enumerator</a>
            <p class="mt-2 px-2 text-xs font-semibold uppercase text-slate-500">Organizations</p>
            <a class="rounded px-2 py-2 hover:bg-brand-50" href="{{ route('request.track') }}">Track a request</a>
            @auth<a class="rounded px-2 py-2 font-medium hover:bg-brand-50" href="{{ auth()->user()->homeRoute() }}">My account</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded px-2 py-2 text-left hover:bg-brand-50">Sign out</button></form>@else<a class="rounded px-2 py-2 font-medium hover:bg-brand-50" href="{{ route('login') }}">Sign in</a>@endauth
            <a class="mt-2 rounded-md bg-accent-500 px-3 py-2 text-center font-semibold text-white" href="{{ route('request.create') }}">Request Recruitment Support</a></div>
    </nav>
</header>
<main id="main">
    <div class="mx-auto max-w-7xl px-4 pt-6">@include('partials.flash')</div>
    @yield('content')
</main>
<footer class="mt-20 bg-brand-900 text-brand-100">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 text-sm sm:grid-cols-2 lg:grid-cols-4">
        <div><p class="flex items-center gap-2 text-lg font-bold text-white"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white"><img src="{{ \App\Support\Brand::svg('favicon') }}" alt="" class="h-6 w-6"></span>AFREN</p><p class="mt-3 text-brand-200">Africa Researcher &amp; Enumerator Network. Vetted research talent across the continent, coordinated centrally.</p>
            <ul class="mt-4 flex gap-2">@foreach(\App\Support\Site::socials() as $k => $url)<li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-800 hover:bg-accent-500 hover:text-white" aria-label="{{ \App\Support\Site::label($k) }}">@include('partials.social-icon', ['name' => $k])</a></li>@endforeach</ul></div>
        <div><p class="font-semibold text-white">Organizations</p><ul class="mt-3 space-y-2"><li><a class="hover:underline" href="{{ route('packages') }}">Monthly packages</a></li><li><a class="hover:underline" href="{{ route('request.create') }}">Request recruitment support</a></li><li><a class="hover:underline" href="{{ route('request.track') }}">Track a request</a></li><li><a class="hover:underline" href="{{ route('login') }}">Sign in</a></li></ul></div>
        <div><p class="font-semibold text-white">Professionals</p><ul class="mt-3 space-y-2"><li><a class="hover:underline" href="{{ route('register.create', 'researcher') }}">Register as a Researcher</a></li><li><a class="hover:underline" href="{{ route('register.create', 'enumerator') }}">Register as an Enumerator</a></li></ul></div>
        <div><p class="font-semibold text-white">Company</p><ul class="mt-3 space-y-2"><li><a class="hover:underline" href="{{ route('about') }}">About</a></li><li><a class="hover:underline" href="{{ route('how') }}">How it works</a></li><li><a class="hover:underline" href="{{ route('contact') }}">Contact us</a></li><li><a class="hover:underline" href="{{ route('privacy') }}">Privacy Policy</a></li><li><a class="hover:underline" href="{{ route('terms') }}">Terms &amp; Conditions</a></li></ul></div>
    </div>
    <p class="border-t border-white/10 py-5 text-center text-xs text-brand-300">© {{ date('Y') }} AFREN. All rights reserved.</p>
</footer>
@include('partials.wizard-script')
</body></html>
