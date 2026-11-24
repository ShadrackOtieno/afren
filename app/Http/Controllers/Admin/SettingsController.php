<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Country, Setting, TaxRule};
use App\Services\Audit;
use App\Support\{Brand, Site};
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit() { return view('admin.settings', ['rules' => TaxRule::orderBy('country_code')->get(), 'countries' => Country::orderBy('name')->get(),
        'site' => Site::all(), 'brand' => Brand::colors(), 'brandDefaults' => Brand::DEFAULTS, 'notify' => Setting::get('notify_emails'), 'instructions' => Setting::get('payment_instructions')]); }

    public function update(Request $r)
    {
        $d = $r->validate(['notify_emails' => 'nullable|string|max:500', 'payment_instructions' => 'nullable|string|max:3000']);
        Setting::put('notify_emails', $d['notify_emails'] ?? ''); Setting::put('payment_instructions', $d['payment_instructions'] ?? '');
        Audit::log('settings.updated');
        return back()->with('status', 'Settings saved.');
    }

    public function storeTax(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:100', 'country_code' => 'nullable|exists:countries,iso2', 'rate' => 'required|numeric|between:0,100']);
        $t = TaxRule::create($d + ['is_active' => true]); Audit::log('tax_rule.created', $t);
        return back()->with('status', 'Tax rule added.');
    }

    public function destroyTax(TaxRule $taxRule) { Audit::log('tax_rule.deleted', $taxRule); $taxRule->delete(); return back()->with('status', 'Tax rule removed.'); }

    public function updateBrand(Request $r)
    {
        $d = $r->validate(['primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'], 'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/']],
            ['primary.regex' => 'Enter the primary colour as a 6-digit hex value, e.g. #12355b.', 'accent.regex' => 'Enter the accent colour as a 6-digit hex value, e.g. #bf5410.']);
        // Buttons use white text on these colours, so they must stay readable.
        $pc = Brand::contrastWithWhite($d['primary']); $ac = Brand::contrastWithWhite($d['accent']);
        if ($pc < 4.5) { return back()->withInput()->withErrors(['primary' => "That primary colour is too light for white button text (contrast {$pc}:1, minimum 4.5:1). Pick a darker shade."]); }
        if ($ac < 3.0) { return back()->withInput()->withErrors(['accent' => "That accent colour is too light for white button text (contrast {$ac}:1, minimum 3:1). Pick a darker shade."]); }
        Brand::save($d['primary'], $d['accent']);
        Audit::log('settings.brand_updated', null, $d);
        $note = $ac < 4.5 ? " Note: white text on the accent colour has a contrast of {$ac}:1; below 4.5:1 can be hard to read for some people." : '';
        return back()->with('status', 'Brand colours saved and applied across the site.'.$note);
    }

    public function resetBrand()
    {
        Brand::reset(); Audit::log('settings.brand_reset');
        return back()->with('status', 'Brand colours reset to the defaults (dark blue and orange).');
    }

    public function updateSite(Request $r)
    {
        $d = $r->validate([
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-\s.]{5,40}$/'], 'email' => 'nullable|email|max:190',
            'whatsapp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\s\-]{6,25}$/'],
            'facebook' => 'nullable|url|max:255', 'x' => 'nullable|url|max:255', 'linkedin' => 'nullable|url|max:255', 'instagram' => 'nullable|url|max:255', 'youtube' => 'nullable|url|max:255',
        ], ['phone.regex' => 'Use digits, spaces, + and dashes only, e.g. +254 700 000 000.', 'whatsapp.regex' => 'Use the number with country code, e.g. +254700000000.']);
        Site::save($d);
        Audit::log('settings.site_updated');
        return back()->with('status', 'Contact details and social links saved.');
    }
}
