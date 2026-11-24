<?php
namespace App\Http\Controllers;

use App\Models\{Country, Organization, Package, RecruitmentRequest};
use App\Services\{Notifier, SubscriptionService};
use App\Support\RequestForm;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

/** Signed-in organization portal. */
class SubscriptionPortalController extends Controller
{
    private function org(): Organization
    {
        return Organization::where('user_id', auth()->id())->firstOrFail();
    }

    public function show()
    {
        $org = $this->org();
        $subs = $org->subscriptions()->with(['package', 'pendingPackage', 'events', 'invoices.payments'])->latest()->get();
        return view('public.portal', ['org' => $org, 'subs' => $subs, 'packages' => Package::where('is_active', true)->orderBy('sort')->get()]);
    }

    public function change(Request $request, int $subscription, SubscriptionService $svc)
    {
        $sub = $this->org()->subscriptions()->findOrFail($subscription);
        $new = Package::findOrFail($request->validate(['package_id' => 'required|integer'])['package_id']);
        $res = $svc->changePackage($sub, $new);
        return back()->with('status', $res === 'upgraded' ? 'Upgraded. A prorated invoice (if any) has been emailed.' : "Downgrade scheduled for your next renewal.");
    }

    public function cancel(int $subscription, SubscriptionService $svc)
    {
        $svc->cancel($this->org()->subscriptions()->findOrFail($subscription));
        return back()->with('status', 'Cancellation recorded. Access continues until the end of the paid period.');
    }

    public function resume(int $subscription, SubscriptionService $svc)
    {
        $svc->resume($this->org()->subscriptions()->findOrFail($subscription));
        return back()->with('status', 'Subscription resumed.');
    }

    /** Requests this organisation submitted under its subscription(s). */
    private function requestsOf(Organization $org)
    {
        return RecruitmentRequest::whereIn('subscription_id', $org->subscriptions()->pluck('id'));
    }

    public function requests()
    {
        $org = $this->org();
        return view('public.portal-requests', ['org' => $org, 'requests' => $this->requestsOf($org)->with('shortlists')->latest()->paginate(15),
            'active' => $org->subscriptions()->whereIn('status', ['active', 'past_due'])->exists()]);
    }

    public function showRequest(string $reference)
    {
        $org = $this->org();
        $req = $this->requestsOf($org)->where('reference', $reference)->with(['shortlists.professional', 'invoices', 'subscription.package'])->firstOrFail();
        return view('public.portal-request', ['org' => $org, 'r' => $req]);
    }

    public function editRequest(string $reference)
    {
        $org = $this->org();
        $req = $this->requestsOf($org)->where('reference', $reference)->firstOrFail();
        abort_unless(in_array($req->status, ['new', 'under_review']), 403, 'This request can no longer be edited because work has started. Please contact AFREN.');
        return view('public.portal-request-edit', ['org' => $org, 'req' => $req, 'countries' => Country::orderBy('name')->get()]);
    }

    public function updateRequest(Request $request, string $reference)
    {
        $org = $this->org();
        $req = $this->requestsOf($org)->where('reference', $reference)->firstOrFail();
        abort_unless(in_array($req->status, ['new', 'under_review']), 403);
        $request->merge(['contact_phone' => ltrim(preg_replace('/\D/', '', (string) $request->contact_phone), '0')]);
        $d = $request->validate(RequestForm::rules(false));
        $attrs = RequestForm::attributes($d);
        if ($request->hasFile('document')) {
            if ($req->document_path) { Storage::delete($req->document_path); }
            $attrs['document_path'] = $request->file('document')->storeAs('request-docs', Str::uuid().'.'.$request->file('document')->extension());
        }
        $req->update($attrs);
        Notifier::admins("Request {$req->reference} was updated by the client", "{$req->org_name} edited \"{$req->project_title}\".\n".route('admin.requests.show', $req));
        return redirect()->route('portal.requests.show', $req->reference)->with('status', 'Request updated. AFREN has been notified.');
    }
}
