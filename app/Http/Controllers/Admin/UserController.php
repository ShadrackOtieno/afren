<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index() { return view('admin.users', ['users' => User::orderBy('name')->get()]); }

    public function store(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:120', 'email' => 'required|email|unique:users', 'role' => 'required|in:super_admin,admin,recruiter,finance',
            'password' => ['required', Password::min(10)->mixedCase()->numbers()]]);
        $u = User::create($d + ['is_active' => true]); Audit::log('user.created', $u, ['role' => $d['role']]);
        return back()->with('status', 'Staff account created.');
    }

    public function toggle(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'You cannot deactivate your own account.');
        $user->update(['is_active' => ! $user->is_active]); Audit::log('user.toggled', $user);
        return back()->with('status', 'Account '.($user->is_active ? 'activated' : 'deactivated').'.');
    }
}
