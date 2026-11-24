@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
<h1 class="text-2xl font-bold">Settings &amp; tax rules</h1>
<section class="mt-4 rounded-xl bg-white p-5"><h2 class="font-semibold">Contact details &amp; social links</h2>
<p class="text-sm text-slate-600">Shown in the top bar of every public page and in the footer. Leave a field empty to hide it.</p>
<form method="POST" action="{{ route('admin.settings.site') }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@csrf @method('PUT')
<x-field name="phone" label="Phone number" placeholder="+254 700 000 000" :value="$site['phone']"/>
<x-field name="email" label="Public email" type="email" :value="$site['email']"/>
<x-field name="whatsapp" label="WhatsApp number" placeholder="+254700000000" :value="$site['whatsapp']"/>
<x-field name="facebook" label="Facebook URL" type="url" placeholder="https://facebook.com/…" :value="$site['facebook']"/>
<x-field name="x" label="X (Twitter) URL" type="url" placeholder="https://x.com/…" :value="$site['x']"/>
<x-field name="linkedin" label="LinkedIn URL" type="url" placeholder="https://linkedin.com/company/…" :value="$site['linkedin']"/>
<x-field name="instagram" label="Instagram URL" type="url" placeholder="https://instagram.com/…" :value="$site['instagram']"/>
<x-field name="youtube" label="YouTube URL" type="url" placeholder="https://youtube.com/@…" :value="$site['youtube']"/>
<div class="flex items-end"><button class="rounded bg-brand-700 px-5 py-2 text-sm text-white">Save</button></div></form></section>
<section class="mt-4 rounded-xl bg-white p-5" x-data="{ p: '{{ old('primary', $brand['primary']) }}', a: '{{ old('accent', $brand['accent']) }}',
    lum(h) { const c = [1, 3, 5].map(i => parseInt(h.substr(i, 2), 16) / 255).map(v => v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4)); return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2]; },
    ratio(h) { return /^#[0-9a-f]{6}$/i.test(h) ? (1.05 / (this.lum(h) + 0.05)).toFixed(1) : '–'; } }">
    <h2 class="font-semibold">Brand colours</h2><p class="text-sm text-slate-600">Applied to the whole site, the admin area and the illustrations. Lighter and darker shades are generated automatically.</p>
    <form method="POST" action="{{ route('admin.settings.brand') }}" class="mt-4 grid gap-6 md:grid-cols-2">@csrf @method('PUT')
        <div class="space-y-4">
            <div><label class="block text-sm font-medium" for="primary">Primary (dark blue)</label><div class="mt-1 flex items-center gap-2"><input type="color" x-model="p" class="h-10 w-14 rounded border" aria-label="Pick primary colour"><input id="primary" name="primary" x-model="p" maxlength="7" class="w-32 rounded border-slate-300 font-mono text-sm"><span class="text-xs text-slate-500">white-text contrast <b x-text="ratio(p)"></b>:1 (min 4.5)</span></div></div>
            <div><label class="block text-sm font-medium" for="accent">Accent (orange)</label><div class="mt-1 flex items-center gap-2"><input type="color" x-model="a" class="h-10 w-14 rounded border" aria-label="Pick accent colour"><input id="accent" name="accent" x-model="a" maxlength="7" class="w-32 rounded border-slate-300 font-mono text-sm"><span class="text-xs text-slate-500">white-text contrast <b x-text="ratio(a)"></b>:1 (min 3)</span></div></div>
            <div class="flex gap-2"><button class="rounded bg-brand-700 px-4 py-2 text-sm text-white">Save colours</button><button type="submit" form="brandReset" class="rounded border px-4 py-2 text-sm">Reset to defaults</button></div>
        </div>
        <div class="rounded-lg border border-slate-200 p-4"><p class="text-xs font-semibold uppercase text-slate-500">Preview (before saving)</p>
            <div class="mt-3 rounded-lg p-4 text-white" :style="`background:${p}`"><p class="font-display text-xl">Vetted researchers across Africa</p><button type="button" class="mt-3 rounded px-4 py-2 text-sm font-semibold text-white" :style="`background:${a}`">Request Recruitment Support</button></div>
            <div class="mt-3 flex gap-2 text-sm"><span class="rounded px-3 py-1 text-white" :style="`background:${p}`">Primary</span><span class="rounded px-3 py-1 text-white" :style="`background:${a}`">Accent</span><span class="px-3 py-1 font-medium" :style="`color:${a}`">Accent link</span></div></div>
    </form>
    <form id="brandReset" method="POST" action="{{ route('admin.settings.brand.reset') }}">@csrf</form>
</section>
<div class="mt-6 grid gap-6 lg:grid-cols-2">
<form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-3 rounded-xl bg-white p-5">@csrf @method('PUT')<h2 class="font-semibold">Notifications &amp; payment instructions</h2>
<x-field name="notify_emails" label="Admin notification emails (comma-separated; blank = all staff)" :value="$notify"/>
<x-textarea name="payment_instructions" label="Payment instructions included in invoice emails" rows="5" :value="$instructions"/><button class="rounded bg-brand-700 px-4 py-2 text-sm text-white">Save</button></form>
<section class="space-y-3 rounded-xl bg-white p-5"><h2 class="font-semibold">Tax rules</h2><p class="text-sm text-slate-600">A rule for the client's country applies first; otherwise the rule with no country (default); otherwise 0%. Admins can still override the rate on each direct invoice.</p>
<ul class="divide-y text-sm">@forelse($rules as $t)<li class="flex items-center justify-between py-2"><span>{{ $t->name }} · {{ $t->country_code ?? 'Default' }} · {{ $t->rate + 0 }}%</span><form method="POST" action="{{ route('admin.settings.tax.destroy', $t) }}">@csrf @method('DELETE')<button class="text-red-600 underline">Remove</button></form></li>@empty<li class="py-2 text-slate-500">No tax rules (0% applied).</li>@endforelse</ul>
<form method="POST" action="{{ route('admin.settings.tax.store') }}" class="grid gap-3 border-t pt-3 sm:grid-cols-3">@csrf<x-field name="name" label="Name (e.g. VAT)" required/><x-select name="country_code" label="Country" :options="$countries->pluck('name', 'iso2')" placeholder="Default (all)"/><x-field name="rate" label="Rate %" type="number" step="0.01" required/><button class="rounded bg-slate-800 px-4 py-2 text-sm text-white sm:col-span-3">Add rule</button></form></section></div>
@endsection
