<?php
namespace App\Http\Controllers;

use App\Models\{Country, Professional};
use App\Services\ScoringService;
use App\Support\ProfessionalForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Signed-in researcher/enumerator managing their own profile. */
class ProfileController extends Controller
{
    private function pro(): Professional { return Professional::where('user_id', auth()->id())->firstOrFail(); }

    public function edit() { return view('public.profile', ['pro' => $this->pro(), 'type' => $this->pro()->category, 'countries' => Country::orderBy('name')->get()]); }

    public function update(Request $request, ScoringService $scoring)
    {
        $p = $this->pro();
        $request->merge(['email' => $p->email, 'phone' => ltrim(preg_replace('/\D/', '', (string) $request->phone), '0')]); // email is the login; not editable here
        $d = $request->validate(ProfessionalForm::rules($request, $p));
        $attrs = ProfessionalForm::attributes($d) + ['is_active' => $request->boolean('visible')];
        if ($request->hasFile('cv')) {
            if ($p->cv_path) { Storage::delete($p->cv_path); }
            $attrs['cv_path'] = $request->file('cv')->storeAs('cvs', Str::uuid().'.'.$request->file('cv')->extension());
        }
        if ($request->hasFile('documents')) {
            $docs = $p->documents ?? [];
            foreach ($request->file('documents') as $f) { $docs[] = $f->storeAs('professional-docs', Str::uuid().'.'.$f->extension()); }
            $attrs['documents'] = $docs;
        }
        $p->update($attrs);
        $p->user->update(['name' => $p->full_name]);
        $scoring->refresh($p);
        return back()->with('status', 'Profile updated. Your score was recalculated.');
    }

    public function cv()
    {
        $p = $this->pro();
        abort_unless($p->cv_path && Storage::exists($p->cv_path), 404);
        return Storage::download($p->cv_path, 'my-cv.'.pathinfo($p->cv_path, PATHINFO_EXTENSION));
    }

    public function removeDocument(int $i)
    {
        $p = $this->pro(); $docs = $p->documents ?? [];
        if (isset($docs[$i])) { Storage::delete($docs[$i]); unset($docs[$i]); $p->update(['documents' => array_values($docs)]); }
        return back()->with('status', 'Document removed.');
    }
}
