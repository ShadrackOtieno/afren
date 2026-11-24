<?php
namespace App\Http\Controllers;

use App\Models\{Country, Organization, Package, User};
use App\Services\{Notifier, SubscriptionService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PackageController extends Controller
{
    public function index() { return view('public.packages', ['packages' => Package::where('is_active', true)->orderBy('sort')->get()]); }

    public function subscribe(Package $package)
    {
        abort_unless($package->is_active, 404);
        return view('public.subscribe', ['package' => $package, 'countries' => Country::orderBy('name')->get(), 'org' => auth()->user()?->organization]);
    }

    /** Checkout: signed-in organizations subscribe directly; new organizations create their account here. Activation happens on payment confirmation. */
    public function store(Request $request, Package $package, SubscriptionService $subs)
    {
        abort_unless($package->is_active, 404);
        $user = auth()->user();
        if ($user) {
            if ($user->role !== 'organization' || ! $user->organization) {
                return redirect()->route('packages')->with('error', 'Please sign out and subscribe with an organization account.');
            }
            $request->validate(['terms' => 'accepted']);
            $org = $user->organization;
        } else {
            $d = $request->validate([
                'name' => 'required|string|max:190', 'type' => ['required', Rule::in(config('afren.org_types'))],
                'country_code' => ['required', Rule::exists('countries', 'iso2')], 'location' => 'nullable|string|max:150',
                'contact_name' => 'required|string|max:150', 'email' => 'required|email|max:190|unique:users,email',
                'dial_code' => ['required', Rule::exists('countries', 'dial_code')], 'phone' => 'required|digits_between:6,14',
                'website' => 'nullable|url|max:190', 'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
                'terms' => 'accepted', 'hp' => 'prohibited',
            ], ['email.unique' => 'An account with this email already exists. Please sign in to subscribe.']);
            $org = DB::transaction(function () use ($d) {
                $u = User::create(['name' => $d['contact_name'], 'email' => strtolower($d['email']), 'password' => $d['password'], 'role' => 'organization', 'is_active' => true]);
                unset($d['password'], $d['password_confirmation'], $d['terms'], $d['hp']);
                return Organization::create($d + ['email' => strtolower($d['email']), 'user_id' => $u->id]);
            });
            Auth::login($org->user); $request->session()->regenerate();
        }

        if ($org->subscriptions()->whereIn('status', ['pending', 'active', 'past_due'])->exists()) {
            return redirect()->route('portal.show')->with('error', 'You already have a subscription. Change plan or cancel it from here.');
        }
        $subs->subscribe($org, $package);
        Notifier::admins('New subscription pending payment', "{$org->name} subscribed to {$package->name}. Confirm payment to activate.", ['super_admin', 'admin', 'finance']);
        Notifier::to($org->email, 'Your AFREN subscription', "Thank you for choosing {$package->name}. An invoice has been sent to this address. Sign in to manage your subscription: ".route('login'));
        return redirect()->route('portal.show')->with('status', 'Subscription created. Your first invoice has been emailed; your plan activates once payment is confirmed.');
    }
}
