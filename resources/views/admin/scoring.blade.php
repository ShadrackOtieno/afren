@extends('layouts.admin')
@section('title', 'Scoring rules')
@section('content')
<h1 class="text-2xl font-bold">Scoring rules</h1>
<p class="mt-1 text-sm text-slate-600">Weights are relative: scores are normalised to a maximum of 100 across active criteria. Saving recalculates every profile.</p>
<form method="POST" action="{{ route('admin.scoring.update') }}" class="mt-4 max-w-3xl space-y-4 rounded-xl bg-white p-5">@csrf @method('PUT')
<table class="w-full text-sm"><thead class="text-left text-slate-500"><tr><th>Active</th><th>Label</th><th>Weight</th></tr></thead><tbody class="divide-y">
@foreach($criteria as $c)<tr><td class="py-2"><input type="checkbox" name="criteria[{{ $c->id }}][is_active]" value="1" @checked($c->is_active)></td>
<td><input name="criteria[{{ $c->id }}][label]" value="{{ old("criteria.$c->id.label", $c->label) }}" class="w-full rounded border-slate-300 text-sm"><span class="text-xs text-slate-500">{{ $c->description }}</span></td>
<td><input type="number" min="0" max="100" name="criteria[{{ $c->id }}][weight]" value="{{ old("criteria.$c->id.weight", $c->weight) }}" class="w-20 rounded border-slate-300 text-sm"></td></tr>@endforeach</tbody></table>
<x-field name="min_eligible_score" label="Minimum score for eligibility (0 = no minimum)" type="number" step="0.1" min="0" max="100" :value="$min" class="max-w-xs"/>
<button class="rounded bg-brand-700 px-5 py-2 text-white">Save &amp; recalculate</button></form>
@endsection
