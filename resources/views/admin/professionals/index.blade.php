@extends('layouts.admin')
@section('title', 'Professionals')
@section('content')
<h1 class="text-2xl font-bold">Professionals</h1>
<form method="GET" class="mt-4 grid gap-3 rounded-xl bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
    <x-field name="q" label="Search" :value="request('q')"/>
    <x-select name="country" label="Country" :options="$countries->pluck('name', 'iso2')" :value="request('country')" placeholder="Any"/>
    <x-select name="category" label="Category" :options="config('afren.categories')" :value="request('category')" placeholder="Any"/>
    <x-select name="verification" label="Verification" :options="['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected']" :value="request('verification')" placeholder="Any"/>
    <x-select name="education" label="Min education" :options="collect(array_keys(config('afren.education_levels')))->mapWithKeys(fn($k) => [$k => ucfirst($k)])" :value="request('education')" placeholder="Any"/>
    <x-field name="min_years" label="Min years exp." type="number" :value="request('min_years')"/>
    <x-field name="min_score" label="Min profile score" type="number" :value="request('min_score')"/>
    <x-select name="availability" label="Availability" :options="['available' => 'Available', 'limited' => 'Limited', 'unavailable' => 'Unavailable']" :value="request('availability')" placeholder="Any"/>
    <x-field name="language" label="Language" :value="request('language')"/><x-field name="skill" label="Skill" :value="request('skill')"/><x-field name="tool" label="Tool" :value="request('tool')"/>
    <div class="flex items-end gap-2"><button class="rounded bg-brand-700 px-4 py-2 text-sm text-white">Filter</button><a href="{{ route('admin.professionals.index') }}" class="px-3 py-2 text-sm underline">Reset</a></div>
</form>
<div class="mt-4 overflow-x-auto rounded-xl bg-white"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left"><tr><th class="p-3">Name</th><th>Category</th><th>Country</th><th>Exp.</th><th>Education</th><th>Verification</th><th>Profile score</th></tr></thead><tbody class="divide-y">
@forelse($pros as $p)<tr><td class="p-3"><a class="text-brand-700 hover:underline" href="{{ route('admin.professionals.show', $p) }}">{{ $p->full_name }}</a><div class="text-xs text-slate-500">{{ $p->reference }}</div></td><td>{{ $p->category }}</td><td>{{ $p->country_code }}</td><td>{{ $p->years_experience }}y</td><td class="capitalize">{{ $p->education_level }}</td><td class="capitalize">{{ $p->verification_status }}</td><td><strong>{{ $p->profile_score }}</strong></td></tr>
@empty<tr><td colspan="7" class="p-6 text-center text-slate-500">No professionals match.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $pros->links() }}</div>
@endsection
