@extends('layouts.admin')
@section('title', $r->reference)
@section('content')
<a class="text-sm text-brand-700 underline" href="{{ route('admin.requests.index') }}">← Requests</a>
<div class="mt-2 flex flex-wrap items-start justify-between gap-3">
<div><h1 class="text-2xl font-bold">{{ $r->project_title }}</h1><p class="text-sm text-slate-600">{{ $r->reference }} · {{ $r->org_name }} · received {{ $r->created_at->toDayDateTimeString() }}
 · <span class="rounded bg-slate-200 px-2 py-0.5 text-xs">{{ $r->source === 'public' ? 'Direct request (no subscription)' : 'Subscriber: '.$r->subscription?->package?->name }}</span></p></div>
@if(auth()->user()->role !== 'recruiter')<a href="{{ route('admin.invoices.create', $r) }}" class="rounded bg-brand-700 px-4 py-2 text-sm font-medium text-white">Prepare invoice</a>@endif</div>

<div class="mt-4 grid gap-6 lg:grid-cols-3">
<div class="space-y-6 lg:col-span-2">
<section class="rounded-xl bg-white p-5 text-sm space-y-1"><h2 class="font-semibold">Organization</h2>
<p>{{ $r->org_name }} ({{ $r->org_type }}) · {{ $r->org_country }} {{ $r->org_location }}</p><p>{{ $r->contact_name }} · {{ $r->contact_email }} · {{ $r->contact_dial_code }} {{ $r->contact_phone }} {{ $r->website ? '· '.$r->website : '' }}</p></section>
<section class="rounded-xl bg-white p-5 text-sm space-y-2"><h2 class="font-semibold">Project</h2>
<p><b>Sector:</b> {{ $r->sector }} · <b>Countries:</b> {{ implode(', ', $r->project_countries) }} · <b>Duration:</b> {{ $r->duration ?? '—' }}</p>
<p><b>Dates:</b> {{ $r->start_date?->toDateString() ?? '—' }} → {{ $r->end_date?->toDateString() ?? '—' }} · <b>Recruit by:</b> {{ $r->recruitment_deadline?->toDateString() ?? '—' }}</p>
@if($r->project_locations)<p><b>Locations:</b> {{ $r->project_locations }}</p>@endif
<p class="whitespace-pre-line"><b>Description:</b> {{ $r->project_description }}</p><p class="whitespace-pre-line"><b>Objectives / ToR:</b> {{ $r->objectives }}</p>
@if($r->document_path)<p><a class="text-brand-700 underline" href="{{ route('admin.requests.document', $r) }}">Download project brief</a></p>@endif</section>
<section class="rounded-xl bg-white p-5 text-sm space-y-1"><h2 class="font-semibold">Personnel requirements</h2>
<p><b>Need:</b> {{ $r->headcount }} {{ $r->personnel_type }} · min education {{ $r->min_education }} · min {{ $r->min_years }}y experience · travel {{ $r->travel_required ? 'required' : 'not required' }}</p>
<p><b>Skills:</b> {{ implode(', ', $r->technical_skills ?? []) ?: '—' }} · <b>Languages:</b> {{ implode(', ', $r->languages ?? []) ?: '—' }} · <b>Tools:</b> {{ implode(', ', $r->tools ?? []) ?: '—' }}</p>
@foreach(['qualifications_required' => 'Qualifications', 'experience_required' => 'Experience', 'fieldwork_required' => 'Fieldwork', 'local_knowledge' => 'Local knowledge', 'availability_requirements' => 'Availability', 'additional_criteria' => 'Additional criteria'] as $k => $l)@if($r->$k)<p><b>{{ $l }}:</b> {{ $r->$k }}</p>@endif @endforeach</section>
<section class="rounded-xl bg-white p-5 text-sm space-y-1"><h2 class="font-semibold">Commercial</h2>
<p><b>Budget:</b> {{ $r->budget_min ? number_format($r->budget_min, 2) : '—' }} – {{ $r->budget_max ? number_format($r->budget_max, 2) : '—' }} {{ $r->budget_currency }} · <b>Arrangement:</b> {{ $r->arrangement ?? '—' }}</p>@if($r->additional_services)<p><b>Additional services:</b> {{ $r->additional_services }}</p>@endif</section>

<section class="rounded-xl bg-white p-5 text-sm"><h2 class="font-semibold">Candidate matching</h2>
<form method="GET" class="mt-2 flex flex-wrap items-end gap-3"><input type="hidden" name="match" value="1">
<x-field name="country" label="Candidate country (ISO2)" :value="request('country')" maxlength="2"/><x-field name="min_score" label="Min job fit" type="number" :value="request('min_score')"/>
<label class="flex items-center gap-2 pb-2"><input type="checkbox" name="verified" value="1" @checked(request('verified'))> Verified only</label><button class="rounded bg-brand-700 px-4 py-2 text-white">Find &amp; rank matches</button></form>
@if($matches !== null)
<div class="mt-4 overflow-x-auto"><table class="min-w-full"><thead class="text-left text-slate-500"><tr><th>Candidate</th><th>Job fit</th><th>Profile score</th><th>Why</th><th></th></tr></thead><tbody class="divide-y">
@forelse($matches as $m)@php($p = $m['professional'])<tr class="align-top"><td class="py-2"><a class="text-brand-700 underline" href="{{ route('admin.professionals.show', $p) }}">{{ $p->full_name }}</a><div class="text-xs text-slate-500">{{ $p->category }} · {{ $p->country_code }} · {{ $p->verification_status }}</div></td>
<td class="font-bold {{ $m['meets_basics'] ? '' : 'text-slate-400' }}">{{ $m['score'] }}</td><td>{{ $p->profile_score }}</td>
<td class="text-xs text-slate-600">@foreach($m['breakdown'] as $b){{ $b['key'] }} {{ $b['points'] }}/{{ $b['weight'] }}@if(! $loop->last) · @endif @endforeach @if(! $m['meets_basics'])<div class="text-red-600">Category/availability mismatch</div>@endif</td>
<td>@if(in_array($p->id, $shortlisted))<span class="text-xs text-brand-700">Shortlisted</span>@else<form method="POST" action="{{ route('admin.requests.shortlist', $r) }}">@csrf<input type="hidden" name="professional_id" value="{{ $p->id }}"><button class="rounded border px-2 py-1 text-xs">Shortlist</button></form>@endif</td></tr>
@empty<tr><td colspan="5" class="py-4 text-slate-500">No candidates found.</td></tr>@endforelse</tbody></table></div>@endif</section>

<section class="rounded-xl bg-white p-5 text-sm"><h2 class="font-semibold">Shortlist &amp; recruitment tracking</h2>
@forelse($r->shortlists as $s)<form method="POST" action="{{ route('admin.shortlists.update', $s) }}" class="mt-3 flex flex-wrap items-end gap-2 border-t pt-3">@csrf @method('PUT')
<div class="w-48"><a class="font-medium text-brand-700 underline" href="{{ route('admin.professionals.show', $s->professional) }}">{{ $s->professional->full_name }}</a><div class="text-xs text-slate-500">fit {{ $s->job_score }}</div></div>
<select name="status" class="rounded border-slate-300 text-sm">@foreach(['shortlisted', 'contacted', 'accepted', 'declined', 'hired'] as $st)<option @selected($s->status === $st)>{{ $st }}</option>@endforeach</select>
<input name="notes" value="{{ $s->notes }}" placeholder="Notes" class="flex-1 rounded border-slate-300 text-sm"><button class="rounded bg-slate-800 px-3 py-1.5 text-white">Save</button>
<button form="rm{{ $s->id }}" class="text-red-600 underline">Remove</button></form><form id="rm{{ $s->id }}" method="POST" action="{{ route('admin.shortlists.destroy', $s) }}">@csrf @method('DELETE')</form>
@empty<p class="mt-2 text-slate-500">No one shortlisted yet.</p>@endforelse
@if($r->shortlists->count())<p class="mt-3 text-xs text-slate-500">Hired: {{ $r->shortlists->where('status', 'hired')->count() }} of {{ $r->headcount }} needed.</p>@endif</section>
</div>

<aside class="space-y-6">
<form method="POST" action="{{ route('admin.requests.update', $r) }}" class="space-y-3 rounded-xl bg-white p-5">@csrf @method('PUT')<h2 class="font-semibold">Review &amp; assignment</h2>
<x-select name="status" label="Status" :options="config('afren.request_statuses')" :value="$r->status" :placeholder="null"/>
<x-select name="assigned_to" label="Assigned to" :options="$staff->pluck('name', 'id')" :value="$r->assigned_to" placeholder="Unassigned"/>
<x-textarea name="admin_notes" label="Internal notes" rows="4" :value="$r->admin_notes"/><button class="rounded bg-brand-700 px-4 py-2 text-sm text-white">Save</button></form>
<section class="rounded-xl bg-white p-5 text-sm"><h2 class="font-semibold">Invoices</h2>
@forelse($r->invoices as $i)<p class="mt-2"><a class="text-brand-700 underline" href="{{ route('admin.invoices.show', $i) }}">{{ $i->number }}</a> · {{ number_format($i->total, 2) }} {{ $i->currency }} · <span class="capitalize">{{ $i->status }}</span></p>@empty<p class="mt-2 text-slate-500">No invoice yet. Review the request, then prepare one based on scope.</p>@endforelse</section>
</aside></div>
@endsection
