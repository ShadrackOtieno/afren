<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Invoice, Professional, RecruitmentRequest, Subscription};

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'pros' => Professional::count(), 'prosPending' => Professional::where('verification_status', 'pending')->count(),
            'newRequests' => RecruitmentRequest::where('status', 'new')->count(),
            'openRequests' => RecruitmentRequest::whereNotIn('status', ['completed', 'declined'])->count(),
            'activeSubs' => Subscription::where('status', 'active')->count(), 'pendingSubs' => Subscription::where('status', 'pending')->count(),
            'unpaid' => Invoice::where('status', 'sent')->count(),
            'overdue' => Invoice::where('status', 'sent')->whereDate('due_date', '<', now())->count(),
            'latestRequests' => RecruitmentRequest::latest()->take(6)->get(),
            'latestPros' => Professional::latest()->take(6)->get(),
        ]);
    }
}
