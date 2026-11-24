@php
    $val = function ($k) use ($req) {
        if (! $req) return null; $v = $req->$k;
        return is_array($v) ? implode(', ', $v) : ($v instanceof \Carbon\CarbonInterface ? $v->toDateString() : $v);
    };
    $o = fn ($k, $ok = null) => $val($k) ?? ($ok && $org ? $org->$ok : null);
    $startStep = $errors->any() ? min(array_map(fn ($k) => \App\Support\RequestForm::stepOf($k), $errors->keys())) : 1;
    $edu = collect(array_keys(config('afren.education_levels')))->mapWithKeys(fn ($k) => [$k => ucfirst($k)]);
    $dial = $countries->mapWithKeys(fn ($c) => [$c->dial_code => $c->name.' ('.$c->dial_code.')']);
    $card = 'rounded-2xl border border-slate-200 bg-white p-6 sm:p-8';
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-ref="form" novalidate x-data="wizard({{ $startStep }}, ['Organization', 'Project', 'Personnel', 'Commercial', 'Review'], false)"
      @keydown.enter="if ($event.target.tagName !== 'TEXTAREA' && $event.target.type !== 'submit') { $event.preventDefault(); if (step < total) next(); }"
      @submit="submitGuard($event)" class="mt-8">
    @csrf @if($req) @method('PUT') @endif
    <input type="text" name="hp" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
    @if(request()->boolean('direct'))<input type="hidden" name="direct" value="1">@endif
    <div x-ref="top" class="scroll-mt-24"></div>

    @include('partials.wizard-stepper')

    {{-- STEP 1 --}}
    <section x-ref="s1" data-step="1" x-show="step === 1" class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-900">1. Organization details</h2><p class="mt-1 text-sm text-slate-500">Who is requesting?</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2" x-data="{ d: '{{ old('contact_dial_code', $o('contact_dial_code', 'dial_code')) }}', map: @js($countries->pluck('dial_code', 'iso2')) }">
            <x-field name="org_name" label="Organization name" :value="$o('org_name', 'name')" required/>
            <x-select name="org_type" label="Organization type" :options="config('afren.org_types')" :value="$o('org_type', 'type')" required/>
            <x-select name="org_country" label="Country" :options="$countries->pluck('name', 'iso2')" :value="$o('org_country', 'country_code')" required @change="d = map[$event.target.value] ?? d"/>
            <x-field name="org_location" label="City / location" :value="$o('org_location', 'location')"/>
            <x-field name="contact_name" label="Contact person" :value="$o('contact_name', 'contact_name')" required/>
            <x-field name="contact_email" label="Email address" type="email" :value="$o('contact_email', 'email')" required/>
            <x-select name="contact_dial_code" label="Dialing code" :options="$dial" required x-model="d"/>
            <x-field name="contact_phone" label="Phone number" type="tel" :value="$o('contact_phone', 'phone')" required/>
            <x-field name="website" label="Website (if available)" type="url" placeholder="https://" :value="$o('website', 'website')" class="sm:col-span-2"/>
        </div>
    </section>

    {{-- STEP 2 --}}
    <section x-ref="s2" data-step="2" x-show="step === 2" x-cloak class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-900">2. Project details</h2><p class="mt-1 text-sm text-slate-500">What is the research about, and where?</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <x-field name="project_title" label="Project title" :value="$val('project_title')" required class="sm:col-span-2"/>
            <x-textarea name="project_description" label="Project description" rows="4" :value="$val('project_description')" required class="sm:col-span-2"/>
            <x-textarea name="objectives" label="Research objectives / terms of reference" rows="4" :value="$val('objectives')" required class="sm:col-span-2"/>
            <x-select name="sector" label="Sector / area of research" :options="config('afren.sectors')" :value="$val('sector')" required/>
            <x-field name="duration" label="Project duration" placeholder="e.g. 6 months" :value="$val('duration')"/>
            <div class="sm:col-span-2"><label for="project_countries" class="block text-sm font-medium text-slate-700">Project countries <span class="text-red-600">*</span> <span class="font-normal text-slate-500">(Ctrl/Cmd-click to select several)</span></label>
                <select id="project_countries" name="project_countries[]" multiple size="7" required class="mt-1 w-full rounded-md border border-slate-300 text-sm">@foreach($countries as $c)<option value="{{ $c->iso2 }}" @selected(in_array($c->iso2, old('project_countries', $req?->project_countries ?? [])))>{{ $c->name }}</option>@endforeach</select></div>
            <x-textarea name="project_locations" label="Specific locations (regions, districts, cities)" :value="$val('project_locations')" class="sm:col-span-2"/>
            <x-field name="start_date" label="Expected start date" type="date" :value="$val('start_date')"/><x-field name="end_date" label="Expected end date" type="date" :value="$val('end_date')"/>
            <x-field name="recruitment_deadline" label="Recruitment deadline" type="date" :value="$val('recruitment_deadline')"/>
        </div>
    </section>

    {{-- STEP 3 --}}
    <section x-ref="s3" data-step="3" x-show="step === 3" x-cloak class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-900">3. Personnel requirements</h2><p class="mt-1 text-sm text-slate-500">Who do you need? Skills, languages and tools drive candidate matching.</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <x-select name="personnel_type" label="Who do you need?" :options="['researcher' => 'Researchers', 'enumerator' => 'Enumerators', 'both' => 'Both researchers and enumerators']" :value="$val('personnel_type')" required/>
            <x-field name="headcount" label="Number of professionals needed" type="number" min="1" :value="$val('headcount')" required/>
            <x-select name="min_education" label="Minimum education level" :options="$edu" :value="$val('min_education') ?? 'none'" :placeholder="null"/>
            <x-field name="min_years" label="Minimum years of experience" type="number" min="0" max="60" :value="$val('min_years') ?? 0"/>
            <x-textarea name="qualifications_required" label="Required qualifications" :value="$val('qualifications_required')" class="sm:col-span-2"/>
            <x-textarea name="experience_required" label="Relevant experience" :value="$val('experience_required')" class="sm:col-span-2"/>
            <x-textarea name="fieldwork_required" label="Research / fieldwork experience" :value="$val('fieldwork_required')" class="sm:col-span-2"/>
            <x-field name="technical_skills" label="Technical skills" hint="Comma-separated" :value="$val('technical_skills')"/>
            <x-field name="languages" label="Required languages" hint="Comma-separated, e.g. English, Hausa" :value="$val('languages')"/>
            <x-field name="tools" label="Data collection tools / software" hint="e.g. KoboToolbox, ODK" :value="$val('tools')" class="sm:col-span-2"/>
            <x-textarea name="local_knowledge" label="Required locations or local knowledge" :value="$val('local_knowledge')" class="sm:col-span-2"/>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="travel_required" value="1" @checked(old('travel_required', $req?->travel_required))> Travel is required</label>
            <x-textarea name="availability_requirements" label="Availability requirements" :value="$val('availability_requirements')" class="sm:col-span-2"/>
            <x-textarea name="additional_criteria" label="Additional selection criteria" :value="$val('additional_criteria')" class="sm:col-span-2"/>
        </div>
    </section>

    {{-- STEP 4 --}}
    <section x-ref="s4" data-step="4" x-show="step === 4" x-cloak class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-900">4. Commercial information</h2><p class="mt-1 text-sm text-slate-500">Optional, but it helps us prepare an accurate invoice.</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <x-field name="budget_min" label="Budget from" type="number" step="0.01" min="0" :value="$val('budget_min')"/><x-field name="budget_max" label="Budget to" type="number" step="0.01" min="0" :value="$val('budget_max')"/>
            <x-select name="budget_currency" label="Currency" :options="$countries->pluck('currency')->unique()->sort()->values()" :value="$val('budget_currency')" placeholder="—"/>
            <x-select name="arrangement" label="Preferred recruitment arrangement" :options="config('afren.arrangements')" :value="$val('arrangement')" class="sm:col-span-3"/>
            <x-textarea name="additional_services" label="Additional service requirements" :value="$val('additional_services')" class="sm:col-span-3"/>
            <div class="sm:col-span-3"><label for="document" class="block text-sm font-medium text-slate-700">Project brief / Terms of Reference (PDF/DOC, max 10 MB)</label>
                <input id="document" type="file" name="document" accept=".pdf,.doc,.docx" class="mt-1 block text-sm">@if($req?->document_path)<p class="mt-1 text-xs text-slate-500">A document is already attached. Uploading a new one replaces it.</p>@endif</div>
        </div>
    </section>

    {{-- STEP 5 --}}
    <section x-ref="s5" data-step="5" x-show="step === 5" x-cloak class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-900">5. Review &amp; {{ $req ? 'save' : 'submit' }}</h2><p class="mt-1 text-sm text-slate-500">Check your answers. Use “Edit” on any section to change it.</p>
        @include('partials.wizard-review')
        @unless($req)
        <label class="mt-6 flex items-start gap-2 text-sm"><input type="checkbox" name="consent" value="1" class="mt-1" required @checked(old('consent'))> <span>I consent to AFREN processing this information to review my request, as described in the <a class="underline" href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>. Submitting does not create a subscription or any payment obligation.</span></label>
        @endunless
    </section>

    @include('partials.wizard-buttons', ['submit' => $req ? 'Save changes' : 'Submit request'])
</form>
