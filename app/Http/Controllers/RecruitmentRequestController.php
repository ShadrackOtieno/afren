<?php
namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Organization;
use App\Models\RecruitmentRequest;
use App\Services\Notifier;
use App\Support\RequestForm;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** PUBLIC recruitment request. Needs no account, no login, no subscription, no payment. */
class RecruitmentRequestController extends Controller
{
    public function create(Request $request)
    {
        $org = $request->boolean('direct') ? null : $this->subscriberOrg();
        return view('public.request', ['countries' => Country::orderBy('name')->get(), 'org' => $org, 'req' => null]);
    }

    public function store(Request $request)
    {
        $request->merge(['contact_phone' => ltrim(preg_replace('/\D/', '', (string) $request->contact_phone), '0')]);
        $d = $request->validate(RequestForm::rules(true));

        // A signed-in subscriber's request counts against the package (unless they chose "direct")
        $sub = null;
        if (! $request->boolean('direct') && ($org = $this->subscriberOrg())) {
            $sub = $org->subscriptions()->whereIn('status', ['active', 'past_due'])->with('package')->latest()->first();
            if ($sub && ($cap = $sub->package->max_requests_per_month) !== null && $sub->requestsThisPeriod() >= $cap) {
                return back()->withInput()->withErrors(['subscription' => "Your package allows {$cap} request(s) per billing period and the limit has been reached. You can upgrade your plan, or submit this as a direct request (invoiced separately)."]);
            }
        }

        $req = RecruitmentRequest::create(RequestForm::attributes($d) + [
            'reference' => RecruitmentRequest::newReference(), 'subscription_id' => $sub?->id, 'source' => $sub ? 'subscriber' : 'public',
            'document_path' => $request->hasFile('document') ? $request->file('document')->storeAs('request-docs', Str::uuid().'.'.$request->file('document')->extension()) : null,
            'consent_at' => now(),
        ]);

        Notifier::admins('New recruitment request '.$req->reference, "{$req->org_name} submitted \"{$req->project_title}\" ({$req->headcount} {$req->personnel_type}). Source: {$req->source}.\n".route('admin.requests.show', $req));
        Notifier::to($req->contact_email, "We received your request {$req->reference}", "Thank you, {$req->contact_name}. Your recruitment request \"{$req->project_title}\" has been received. Reference: {$req->reference}. AFREN will review it and send an invoice based on the scope. You do not need an account or subscription.");

        return redirect()->route('request.done')->with('reference', $req->reference);
    }

    public function done() { return view('public.request-done', ['reference' => session('reference')]); }

    public function track(Request $request)
    {
        $found = null;
        if ($request->filled('reference')) {
            $found = RecruitmentRequest::where('reference', strtoupper(trim($request->reference)))->where('contact_email', strtolower(trim((string) $request->email)))->first();
            if (! $found) { session()->now('error', 'No request matches that reference and email.'); }
        }
        return view('public.track', ['found' => $found]);
    }

    private function subscriberOrg(): ?Organization
    {
        $u = auth()->user();
        return $u && $u->role === 'organization' ? $u->organization : null;
    }
}
