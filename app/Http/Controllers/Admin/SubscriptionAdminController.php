<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\{Audit, SubscriptionService};
use Illuminate\Http\Request;

class SubscriptionAdminController extends Controller
{
    public function index(Request $r)
    {
        $q = Subscription::with(['organization', 'package'])->latest();
        $q->when($r->status, fn ($x, $v) => $x->where('status', $v));
        $q->when($r->q, fn ($x, $v) => $x->whereHas('organization', fn ($o) => $o->where('name', 'like', "%$v%")->orWhere('email', 'like', "%$v%")));
        return view('admin.subscriptions.index', ['subs' => $q->paginate(25)->withQueryString()]);
    }

    public function show(Subscription $subscription)
    {
        return view('admin.subscriptions.show', ['s' => $subscription->load(['organization', 'package', 'pendingPackage', 'events', 'invoices.payments', 'requests'])]);
    }

    public function cancel(Subscription $subscription, SubscriptionService $svc)
    {
        $subscription->update(['status' => 'cancelled', 'cancelled_at' => now(), 'ends_at' => now(), 'cancel_at_period_end' => false]);
        $svc->event($subscription, 'cancelled', 'Cancelled immediately by admin');
        Audit::log('subscription.cancelled', $subscription);
        return back()->with('status', 'Subscription cancelled immediately.');
    }

    public function runLifecycle(SubscriptionService $svc)
    {
        $s = $svc->process();
        Audit::log('subscription.lifecycle_run', null, $s);
        return back()->with('status', 'Lifecycle run: '.collect($s)->map(fn ($v, $k) => "$k=$v")->implode(', '));
    }
}
