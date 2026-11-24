<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** One sign-in for everyone (staff, professionals, organizations); the role decides where they land. */
class AuthController extends Controller
{
    public function show() { return auth()->check() ? redirect(auth()->user()->homeRoute()) : view('auth.login'); }

    public function login(Request $request)
    {
        $cred = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::attempt(['email' => strtolower($cred['email']), 'password' => $cred['password'], 'is_active' => 1], $request->boolean('remember'))) {
            $request->session()->regenerate();
            if (Auth::user()->isStaff()) { Audit::log('admin.login'); }
            // Staff may be sent back to the admin page they wanted; others always go to their own home.
            return Auth::user()->isStaff() ? redirect()->intended(Auth::user()->homeRoute()) : redirect(Auth::user()->homeRoute());
        }
        return back()->withErrors(['email' => 'These credentials do not match an active account.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (auth()->user()?->isStaff()) { Audit::log('admin.logout'); }
        Auth::logout();
        $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect()->route('home')->with('status', 'You have been signed out.');
    }
}
