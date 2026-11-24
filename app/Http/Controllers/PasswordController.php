<?php
namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Password};
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordController extends Controller
{
    public function forgot() { return view('auth.forgot'); }

    public function sendLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        Password::sendResetLink($request->only('email')); // same reply whether or not the account exists
        return back()->with('status', 'If an account exists for that email, a password reset link has been sent.');
    }

    public function reset(Request $request, string $token) { return view('auth.reset', ['token' => $token, 'email' => $request->query('email')]); }

    public function update(Request $request)
    {
        $d = $request->validate(['token' => 'required', 'email' => 'required|email', 'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()]]);
        $status = Password::reset($d, function ($user, $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password updated. You can sign in now.')
            : back()->withErrors(['email' => __($status)]);
    }
}
