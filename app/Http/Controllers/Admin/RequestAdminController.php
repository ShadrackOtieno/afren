<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Professional, RecruitmentRequest, Shortlist, User};
use App\Services\{Audit, MatchingService, Notifier};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RequestAdminController extends Controller
{
    public function index(Request $r)
    {
        $q = RecruitmentRequest::with('assignee')->latest();
        $q->when($r->status, fn ($x, $v) => $x->where('status', $v));
        $q->when($r->source, fn ($x, $v) => $x->where('source', $v));
        $q->when($r->assigned_to, fn ($x, $v) => $x->where('assigned_to', $v));
        $q->when($r->q, fn ($x, $v) => $x->where(fn ($w) => $w->where('reference', 'like', "%$v%")->orWhere('org_name', 'like', "%$v%")->orWhere('project_title', 'like', "%$v%")));
        return view('admin.requests.index', ['requests' => $q->paginate(25)->withQueryString(), 'staff' => User::where('is_active', true)->get()]);
    }

    public function show(Request $r, RecruitmentRequest $recruitmentRequest, MatchingService $matcher)
    {
        $req = $recruitmentRequest->load(['shortlists.professional', 'invoices.payments', 'subscription.package', 'assignee']);
        $matches = $r->boolean('match') ? $matcher->rank($req, $r->only(['country', 'verified', 'min_score']), 50) : null;
        $shortlisted = $req->shortlists->pluck('professional_id')->all();
        return view('admin.requests.show', ['r' => $req, 'matches' => $matches, 'shortlisted' => $shortlisted, 'staff' => User::where('is_active', true)->get()]);
    }

    public function update(Request $r, RecruitmentRequest $recruitmentRequest)
    {
        $d = $r->validate(['status' => 'required|in:'.implode(',', array_keys(config('afren.request_statuses'))), 'assigned_to' => 'nullable|exists:users,id', 'admin_notes' => 'nullable|string|max:5000']);
        $old = $recruitmentRequest->status;
        $recruitmentRequest->update($d);
        Audit::log('request.updated', $recruitmentRequest, ['status' => [$old, $d['status']], 'assigned_to' => $d['assigned_to'] ?? null]);
        if ($old !== $d['status'] && in_array($d['status'], ['under_review', 'declined', 'completed'])) {
            Notifier::to($recruitmentRequest->contact_email, "Update on request {$recruitmentRequest->reference}", "The status of your request is now: ".config('afren.request_statuses')[$d['status']].'.');
        }
        return back()->with('status', 'Request updated.');
    }

    public function shortlist(Request $r, RecruitmentRequest $recruitmentRequest, MatchingService $matcher)
    {
        $pro = Professional::findOrFail($r->validate(['professional_id' => 'required|integer'])['professional_id']);
        $cap = $recruitmentRequest->subscription?->package?->max_candidates_per_request;
        if ($cap !== null && $recruitmentRequest->shortlists()->count() >= $cap && ! $recruitmentRequest->shortlists()->where('professional_id', $pro->id)->exists()) {
            return back()->withErrors(['shortlist' => "This subscriber's package allows at most {$cap} shortlisted candidates per request."]);
        }
        Shortlist::firstOrCreate(['recruitment_request_id' => $recruitmentRequest->id, 'professional_id' => $pro->id], ['job_score' => $matcher->score($pro, $recruitmentRequest)['score']]);
        if (in_array($recruitmentRequest->status, ['paid'])) { $recruitmentRequest->update(['status' => 'shortlisting']); }
        Audit::log('request.shortlisted', $recruitmentRequest, ['professional' => $pro->reference]);
        return back()->with('status', "{$pro->full_name} shortlisted.");
    }

    public function updateShortlist(Request $r, Shortlist $shortlist)
    {
        $d = $r->validate(['status' => 'required|in:shortlisted,contacted,accepted,declined,hired', 'notes' => 'nullable|string|max:2000']);
        $shortlist->update($d);
        Audit::log('shortlist.updated', $shortlist, $d);
        return back()->with('status', 'Shortlist entry updated.');
    }

    public function removeShortlist(Shortlist $shortlist) { $shortlist->delete(); return back()->with('status', 'Removed from shortlist.'); }

    public function document(RecruitmentRequest $recruitmentRequest)
    {
        abort_unless($recruitmentRequest->document_path && Storage::exists($recruitmentRequest->document_path), 404);
        return Storage::download($recruitmentRequest->document_path);
    }
}
