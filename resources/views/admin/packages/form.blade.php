@extends('layouts.admin')
@section('title', $package->exists ? 'Edit package' : 'New package')
@section('content')
<h1 class="text-2xl font-bold">{{ $package->exists ? 'Edit '.$package->name : 'New package' }}</h1>
<form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}" class="mt-4 max-w-3xl space-y-4 rounded-xl bg-white p-6">@csrf @if($package->exists) @method('PUT') @endif
<div class="grid gap-4 sm:grid-cols-2">
<x-field name="name" label="Name" :value="$package->name" required/><x-field name="tagline" label="Tagline" :value="$package->tagline"/>
<x-field name="price" label="Monthly price" type="number" step="0.01" min="0" :value="$package->price" required/><x-field name="currency" label="Currency (ISO code)" maxlength="3" :value="$package->currency" required/>
<x-field name="max_requests_per_month" label="Max requests per month" type="number" min="0" :value="$package->max_requests_per_month" hint="Leave empty for no cap"/>
<x-field name="max_candidates_per_request" label="Max shortlisted candidates per request" type="number" min="0" :value="$package->max_candidates_per_request" hint="Leave empty for no cap"/>
<x-textarea name="description" label="Description" :value="$package->description" class="sm:col-span-2"/>
<x-textarea name="features" label="Included services / benefits (one per line)" rows="6" :value="implode(PHP_EOL, $package->features ?? [])" class="sm:col-span-2"/>
<x-field name="sort" label="Display order" type="number" :value="$package->sort ?? 0"/></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $package->is_active))> Active (visible &amp; purchasable)</label>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $package->is_featured))> Highlight as featured</label>
<button class="rounded bg-brand-700 px-5 py-2 text-white">Save package</button></form>
@endsection
