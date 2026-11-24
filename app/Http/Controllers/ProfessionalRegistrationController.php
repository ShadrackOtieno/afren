<?php
namespace App\Http\Controllers;

use App\Models\{Country, Professional, User};
use App\Services\{Notifier, ScoringService};
use App\Support\ProfessionalForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Support\Str;

class ProfessionalRegistrationController extends Controller
{
    public function create(string $type)
    {
        abort_unless(in_array($type, ['researcher', 'enumerator']), 404);
        if (auth()->check()) { return redirect(auth()->user()->homeRoute())->with('status', 'You are already signed in.'); }
        return view('public.register', ['type' => $type, 'pro' => null, 'countries' => Country::orderBy('name')->get()]);
    }

    public function store(Request $request, ScoringService $scoring)
    {
        $request->merge(['phone' => ltrim(preg_replace('/\D/', '', (string) $request->phone), '0')]);
        $data = $request->validate(ProfessionalForm::rules($request));

        $docs = [];
        foreach ($request->file('documents', []) as $f) { $docs[] = $f->storeAs('professional-docs', Str::uuid().'.'.$f->extension()); }
        $cv = $request->file('cv')->storeAs('cvs', Str::uuid().'.'.$request->file('cv')->extension());

        $p = DB::transaction(function () use ($data, $cv, $docs) {
            $user = User::create(['name' => $data['full_name'], 'email' => strtolower($data['email']), 'password' => $data['password'], 'role' => 'professional', 'is_active' => true]);
            return Professional::create(ProfessionalForm::attributes($data) + [
                'user_id' => $user->id, 'reference' => 'PRO-'.strtoupper(Str::random(8)), 'cv_path' => $cv, 'documents' => $docs, 'consent_at' => now(),
            ]);
        });
        $scoring->refresh($p);
        Notifier::admins('New professional registration', "{$p->full_name} ({$p->category}, {$p->country_code}) registered. Ref {$p->reference}. Score {$p->profile_score}.");
        Notifier::to($p->email, 'Welcome to AFREN', "Hello {$p->full_name},\n\nYour AFREN account is ready. Reference: {$p->reference}.\nSign in any time to update your profile: ".route('login'));

        Auth::login($p->user);
        $request->session()->regenerate();
        return redirect()->route('profile.edit')->with('status', "Welcome, {$p->full_name}! Your account is created. Reference {$p->reference}. AFREN will review and verify your profile.");
    }
}
