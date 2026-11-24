@php
    $v = function ($k) use ($pro) { if (! $pro) return null; $x = $pro->$k; return is_array($x) ? implode(', ', $x) : $x; };
    $edu = collect(array_keys(config('afren.education_levels')))->mapWithKeys(fn ($k) => [$k => ucfirst($k)]);
    $dial = $countries->mapWithKeys(fn ($c) => [$c->dial_code => $c->name.' ('.$c->dial_code.')']);
    $card = 'rounded-2xl border border-slate-200 bg-white p-6 sm:p-8';
    $startStep = $errors->any() ? min(array_map(fn ($k) => \App\Support\ProfessionalForm::stepOf($k), $errors->keys())) : 1;
    $titles = ['Personal', 'Education', 'Skills', $pro ? 'Documents' : 'Documents & login', 'Review'];
@endphp
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-ref="form" novalidate
      x-data="wizard({{ $startStep }}, @js($titles), {{ $pro ? 'true' : 'false' }})"
      @keydown.enter="if ($event.target.tagName !== 'TEXTAREA' && $event.target.type !== 'submit') { $event.preventDefault(); if (step < total) next(); }"
      @submit="submitGuard($event)">
    @csrf @if($pro) @method('PUT') @endif <input type="text" name="hp" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
    <div x-ref="top" class="scroll-mt-24"></div>
    @include('partials.flash')
    @include('partials.wizard-stepper')

    <section x-ref="s1" data-step="1" x-show="step === 1" class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-800">Personal &amp; contact</h2><p class="mt-1 text-sm text-slate-500">Who are you and how can we reach you?</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2" x-data="{ d: '{{ old('dial_code', $pro?->dial_code) }}', map: @js($countries->pluck('dial_code', 'iso2')) }">
            <x-field name="full_name" label="Full name" :value="$v('full_name')" required class="sm:col-span-2"/>
            <x-field name="email" label="Email{{ $pro ? ' (your login; contact AFREN to change)' : '' }}" type="email" :value="$v('email')" required :readonly="(bool) $pro"/>
            <x-select name="category" label="Register as" :options="config('afren.categories')" :value="$v('category') ?? $type" required/>
            <x-select name="country_code" label="Country" :options="$countries->pluck('name', 'iso2')" :value="$v('country_code')" required @change="d = map[$event.target.value] ?? d"/>
            <x-field name="location" label="City / region" :value="$v('location')"/>
            <x-select name="dial_code" label="Dialing code" :options="$dial" required x-model="d"/>
            <x-field name="phone" label="Phone number" type="tel" :value="$v('phone')" required/></div></section>

    <section x-ref="s2" data-step="2" x-show="step === 2" x-cloak class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-800">Education &amp; experience</h2><p class="mt-1 text-sm text-slate-500">Your qualifications and years in the field.</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <x-select name="education_level" label="Highest education" :options="$edu" :value="$v('education_level')" required/>
            <x-field name="field_of_study" label="Field of study" :value="$v('field_of_study')"/>
            <x-field name="years_experience" label="Total years of experience" type="number" min="0" max="60" :value="$v('years_experience') ?? 0" required/>
            <x-field name="research_years" label="Years of research experience" type="number" min="0" max="60" :value="$v('research_years') ?? 0" required/>
            <x-field name="fieldwork_years" label="Years of field data collection" type="number" min="0" max="60" :value="$v('fieldwork_years') ?? 0" required/>
            <x-textarea name="qualifications" label="Qualifications" :value="$v('qualifications')" class="sm:col-span-2"/>
            <x-textarea name="experience_summary" label="Relevant experience (projects, roles, countries)" rows="5" :value="$v('experience_summary')" class="sm:col-span-2"/>
            <x-textarea name="certifications" label="Certifications" :value="$v('certifications')" class="sm:col-span-2"/></div></section>

    <section x-ref="s3" data-step="3" x-show="step === 3" x-cloak class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-800">Skills, languages &amp; availability</h2><p class="mt-1 text-sm text-slate-500">These are matched against each project's requirements.</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <x-field name="languages" label="Languages" required hint="Comma-separated, e.g. English, Swahili, French" :value="$v('languages')"/>
            <x-field name="skills" label="Research &amp; fieldwork skills" hint="e.g. Survey design, Qualitative interviews, STATA" :value="$v('skills')"/>
            <x-field name="tools" label="Data collection tools &amp; software" hint="e.g. KoboToolbox, ODK, SurveyCTO" :value="$v('tools')" class="sm:col-span-2"/>
            <x-select name="availability" label="Availability" :options="['available' => 'Available now', 'limited' => 'Limited availability', 'unavailable' => 'Not available']" :value="$v('availability') ?? 'available'" required/>
            <label class="flex items-center gap-2 self-end text-sm"><input type="checkbox" name="willing_to_travel" value="1" @checked(old('willing_to_travel', $pro?->willing_to_travel))> Willing to travel for assignments</label>
            <div class="sm:col-span-2"><label for="preferred_countries" class="block text-sm font-medium">Preferred countries of work <span class="font-normal text-slate-500">(Ctrl/Cmd-click for several)</span></label>
                <select id="preferred_countries" name="preferred_countries[]" multiple size="6" class="mt-1 w-full rounded-md border border-slate-300 text-sm">@foreach($countries as $c)<option value="{{ $c->iso2 }}" @selected(in_array($c->iso2, old('preferred_countries', $pro?->preferred_countries ?? [])))>{{ $c->name }}</option>@endforeach</select></div></div></section>

    <section x-ref="s4" data-step="4" x-show="step === 4" x-cloak class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-800">{{ $pro ? 'Documents & visibility' : 'Documents & login' }}</h2><p class="mt-1 text-sm text-slate-500">{{ $pro ? 'Keep your CV current and control whether recruiters can see you.' : 'Upload your CV and create the password you will sign in with.' }}</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div><label for="cv" class="block text-sm font-medium">CV (PDF/DOC, max 5 MB) @unless($pro)<span class="text-red-600">*</span>@endunless</label><input id="cv" name="cv" type="file" accept=".pdf,.doc,.docx" @required(! $pro) class="mt-1 block w-full text-sm">@if($pro)<p class="mt-1 text-xs text-slate-500">Leave empty to keep your current CV.</p>@endif</div>
            <div><label for="documents" class="block text-sm font-medium">{{ $pro ? 'Add certificates / supporting documents' : 'Certificates / supporting documents' }}</label><input id="documents" name="documents[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="mt-1 block w-full text-sm"></div>
            @unless($pro)<div class="sm:col-span-2 rounded-xl bg-brand-50 p-5"><h3 class="font-semibold text-brand-800">Create your login</h3><p class="mb-4 mt-1 text-sm text-slate-600">Your email and this password let you sign in and update your profile any time.</p>@include('partials.password-fields')</div>
            @else<label class="sm:col-span-2 flex items-start gap-2 rounded-xl bg-brand-50 p-5 text-sm"><input type="checkbox" name="visible" value="1" class="mt-1" @checked(old('visible', $pro->is_active))> <span><b>Visible to recruiters.</b> Untick to pause your profile; AFREN will not consider you for new projects until you turn it back on.</span></label>@endunless
        </div></section>

    <section x-ref="s5" data-step="5" x-show="step === 5" x-cloak class="{{ $card }}">
        <h2 class="font-display text-2xl text-brand-800">Review &amp; {{ $pro ? 'save' : 'submit' }}</h2><p class="mt-1 text-sm text-slate-500">Check your answers. Use “Edit” to change a section.</p>
        @include('partials.wizard-review')
        @unless($pro)<label class="mt-6 flex items-start gap-2 text-sm"><input type="checkbox" name="consent" value="1" class="mt-1" required @checked(old('consent'))> <span>I confirm the information is accurate and consent to AFREN processing my data and sharing my profile with organizations I am shortlisted for, as described in the <a class="underline" href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>.</span></label>@endunless
    </section>

    @include('partials.wizard-buttons', ['submit' => $pro ? 'Save changes' : 'Submit registration'])
</form>
