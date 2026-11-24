<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Admin') · AFREN Admin</title><meta name="robots" content="noindex">
@include('partials.head')</head>
<body class="bg-slate-100 text-slate-800" x-data="{ nav: false }">
@php
$role = auth()->user()->role;
$can = fn (...$r) => $role === 'super_admin' || in_array($role, $r);
@endphp
<div class="flex min-h-screen">
<aside class="fixed inset-y-0 left-0 z-30 w-60 -translate-x-full overflow-y-auto bg-slate-900 p-4 text-sm text-slate-200 transition lg:static lg:translate-x-0" :class="{ 'translate-x-0': nav }">
    <p class="mb-4 text-lg font-extrabold text-white">AFREN Admin</p>
    <nav class="space-y-1" aria-label="Admin">
        @php
$links = [
            ['Dashboard', 'admin.dashboard', ['admin','recruiter','finance']],
            ['Professionals', 'admin.professionals.index', ['admin','recruiter']],
            ['Recruitment requests', 'admin.requests.index', ['admin','recruiter']],
            ['Messages', 'admin.messages', ['admin','recruiter']],
            ['Subscriptions', 'admin.subscriptions.index', ['admin','finance']],
            ['Invoices & payments', 'admin.invoices.index', ['admin','finance']],
            ['Financial reports', 'admin.reports', ['admin','finance']],
            ['Packages', 'admin.packages.index', ['admin']],
            ['Scoring rules', 'admin.scoring', ['admin']],
            ['Settings & tax', 'admin.settings', ['admin']],
            ['Staff users', 'admin.users', []],
            ['Audit log', 'admin.audit', []],
        ];
        @endphp
        @foreach($links as [$label, $route, $roles])
            @if($can(...$roles))<a href="{{ route($route) }}" class="block rounded px-3 py-2 {{ request()->routeIs($route) ? 'bg-brand-700 text-white' : 'hover:bg-slate-800' }}">{{ $label }}</a>@endif
        @endforeach
    </nav>
    <form method="POST" action="{{ route('admin.logout') }}" class="mt-6">@csrf<p class="mb-2 text-xs text-slate-400">{{ auth()->user()->name }} · {{ $role }}</p><button class="rounded border border-slate-600 px-3 py-1.5 text-xs hover:bg-slate-800">Sign out</button></form>
</aside>
<div class="flex-1 min-w-0">
    <div class="flex items-center gap-3 border-b bg-white px-4 py-3 lg:hidden"><button @click="nav = !nav" class="rounded border px-3 py-1 text-sm" aria-label="Toggle menu">Menu</button><span class="font-semibold">@yield('title')</span></div>
    <main class="p-4 lg:p-8">@include('partials.flash')@yield('content')</main>
</div></div></body></html>
