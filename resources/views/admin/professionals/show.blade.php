@extends('layouts.admin')
@section('title', $p->full_name)
@section('content')
<a href="{{ route('admin.professionals.index') }}" class="text-sm text-brand-700 underline">← Professionals</a>
<div class="mt-2 flex flex-wrap items-center justify-between gap-3"><h1 class="text-2xl font-bold">{{ $p->full_name }} <span class="text-sm font-normal text-slate-500">{{ $p->reference }}</span></h1>
<p class="text-3xl font-extrabold text-brand-800">{{ $p->profile_score }}<span class="text-base font-normal text-slate-500"> / 100 profile score</span></p></div>
<div class="mt-4 grid gap-6 lg:grid-cols-3">
<section class="space-y-1 rounded-xl bg-white p-5 text-sm lg:col-span-2">
    <p><b>Email:</b> {{ $p->email }} · <b>Phone:</b> {{ $p->dial_code }} {{ $p->phone }}</p>
    <p><b>Country:</b> {{ $p->country?->name }} {{ $p->location ? '· '.$p->location : '' }} · <b>Category:</b> {{ $p->category }}</p>
    <p><b>Education:</b> {{ ucfirst($p->education_level) }} {{ $p->field_of_study ? '· '.$p->field_of_study : '' }}</p>
    <p><b>Experience:</b> {{ $p->years_experience }}y total · {{ $p->research_years }}y research · {{ $p->fieldwork_years }}y fieldwork</p>
    <p><b>Languages:</b> {{ implode(', ', $p->languages ?? []) }}</p><p><b>Skills:</b> {{ implode(', ', $p->skills ?? []) }}</p><p><b>Tools:</b> {{ implode(', ', $p->tools ?? []) }}</p>
    <p><b>Availability:</b> {{ $p->availability }} · Travel: {{ $p->willing_to_travel ? 'yes' : 'no' }} · Preferred: {{ implode(', ', $p->preferred_countries ?? []) ?: '—' }}</p>
    @if($p->qualifications)<p><b>Qualifications:</b> {{ $p->qualifications }}</p>@endif
    @if($p->experience_summary)<p class="whitespace-pre-line"><b>Experience summary:</b> {{ $p->experience_summary }}</p>@endif
    @if($p->certifications)<p><b>Certifications:</b> {{ $p->certifications }}</p>@endif
    <p class="pt-2"><a class="text-brand-700 underline" href="{{ route('admin.professionals.cv', $p) }}">Download CV</a>
        @foreach($p->documents ?? [] as $i => $d) · <a class="text-brand-700 underline" href="{{ route('admin.professionals.document', [$p, $i]) }}">Document {{ $i + 1 }}</a>@endforeach</p>
    <h2 class="pt-4 font-semibold">Score breakdown <span class="text-xs font-normal text-slate-500">(profile completeness — not job-specific suitability)</span></h2>
    <table class="w-full text-sm"><thead class="text-left text-slate-500"><tr><th>Criterion</th><th>Weight</th><th>Fulfilled</th><th>Points</th></tr></thead><tbody>
    @foreach($p->score_breakdown ?? [] as $b)<tr class="border-t"><td class="py-1">{{ $b['label'] }}</td><td>{{ $b['weight'] }}</td><td><div class="h-2 w-24 rounded bg-slate-200"><div class="h-2 rounded bg-brand-600" style="width: {{ $b['fraction'] * 100 }}%"></div></div></td><td>{{ $b['points'] }} / {{ $b['max_points'] }}</td></tr>@endforeach</tbody></table>
</section>
<aside class="space-y-4">
<form method="POST" action="{{ route('admin.professionals.update', $p) }}" class="space-y-3 rounded-xl bg-white p-5">@csrf @method('PUT')
    <h2 class="font-semibold">Verification &amp; notes</h2>
    <x-select name="verification_status" label="Status" :options="['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected']" :value="$p->verification_status" :placeholder="null"/>
    <x-textarea name="admin_notes" label="Internal notes" :value="$p->admin_notes"/>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($p->is_active)> Active in matching pool</label>
    <button class="rounded bg-brand-700 px-4 py-2 text-sm text-white">Save</button></form>
<form method="POST" action="{{ route('admin.professionals.recalculate', $p) }}">@csrf<button class="rounded border bg-white px-4 py-2 text-sm">Recalculate score</button></form>
<div class="rounded-xl bg-white p-5 text-sm"><h2 class="font-semibold">Shortlisted for</h2>@forelse($p->shortlists as $s)<p><a class="text-brand-700 underline" href="{{ route('admin.requests.show', $s->request) }}">{{ $s->request->reference }}</a> · {{ $s->status }} · fit {{ $s->job_score }}</p>@empty<p class="text-slate-500">Not shortlisted yet.</p>@endforelse</div>
</aside></div>
@endsection
