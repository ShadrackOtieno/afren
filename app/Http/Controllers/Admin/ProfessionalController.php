<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Country, Professional};
use App\Services\{Audit, ScoringService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfessionalController extends Controller
{
    public function index(Request $r)
    {
        $q = Professional::query();
        $q->when($r->q, fn ($x, $v) => $x->where(fn ($w) => $w->where('full_name', 'like', "%$v%")->orWhere('email', 'like', "%$v%")->orWhere('reference', 'like', "%$v%")));
        $q->when($r->country, fn ($x, $v) => $x->where('country_code', $v));
        $q->when($r->category, fn ($x, $v) => $x->where(fn ($w) => $w->where('category', $v)->orWhere('category', 'both')));
        $q->when($r->verification, fn ($x, $v) => $x->where('verification_status', $v));
        $q->when($r->education, fn ($x, $v) => $x->whereIn('education_level', array_keys(array_filter(config('afren.education_levels'), fn ($l) => $l >= (config('afren.education_levels')[$v] ?? 0)))));
        $q->when($r->min_years, fn ($x, $v) => $x->where('years_experience', '>=', (int) $v));
        $q->when($r->min_score, fn ($x, $v) => $x->where('profile_score', '>=', (float) $v));
        $q->when($r->availability, fn ($x, $v) => $x->where('availability', $v));
        foreach (['language' => 'languages', 'skill' => 'skills', 'tool' => 'tools'] as $param => $col) {
            $q->when($r->$param, fn ($x, $v) => $x->where($col, 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $v).'%'));
        }
        $q->orderBy($r->get('sort') === 'newest' ? 'created_at' : 'profile_score', 'desc');
        return view('admin.professionals.index', ['pros' => $q->paginate(25)->withQueryString(), 'countries' => Country::orderBy('name')->get()]);
    }

    public function show(Professional $professional)
    {
        return view('admin.professionals.show', ['p' => $professional->load('shortlists.request')]);
    }

    public function update(Request $r, Professional $professional, ScoringService $scoring)
    {
        $d = $r->validate(['verification_status' => 'required|in:pending,verified,rejected', 'admin_notes' => 'nullable|string|max:3000', 'is_active' => 'nullable|boolean']);
        $professional->update([
            'verification_status' => $d['verification_status'], 'admin_notes' => $d['admin_notes'] ?? null, 'is_active' => $r->boolean('is_active'),
            'verified_at' => $d['verification_status'] === 'verified' ? now() : null,
        ]);
        $scoring->refresh($professional);
        Audit::log('professional.updated', $professional, ['verification' => $d['verification_status']]);
        return back()->with('status', 'Profile updated and score recalculated.');
    }

    public function recalculate(Professional $professional, ScoringService $scoring)
    {
        $scoring->refresh($professional);
        return back()->with('status', 'Score recalculated.');
    }

    public function cv(Professional $professional)
    {
        abort_unless($professional->cv_path && Storage::exists($professional->cv_path), 404);
        Audit::log('professional.cv_downloaded', $professional);
        return Storage::download($professional->cv_path, 'CV-'.$professional->reference.'.'.pathinfo($professional->cv_path, PATHINFO_EXTENSION));
    }

    public function document(Professional $professional, int $i)
    {
        $path = ($professional->documents ?? [])[$i] ?? null;
        abort_unless($path && Storage::exists($path), 404);
        return Storage::download($path);
    }
}
