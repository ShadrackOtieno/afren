<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{ScoringCriterion, Setting};
use App\Services\{Audit, ScoringService};
use Illuminate\Http\Request;

class ScoringController extends Controller
{
    public function edit() { return view('admin.scoring', ['criteria' => ScoringCriterion::orderBy('sort')->get(), 'min' => Setting::get('min_eligible_score', 0)]); }

    public function update(Request $r, ScoringService $svc)
    {
        $d = $r->validate(['criteria' => 'required|array', 'criteria.*.label' => 'required|string|max:150', 'criteria.*.weight' => 'required|integer|between:0,100',
            'min_eligible_score' => 'required|numeric|between:0,100']);
        foreach ($d['criteria'] as $id => $row) {
            ScoringCriterion::whereKey($id)->update(['label' => $row['label'], 'weight' => $row['weight'], 'is_active' => isset($r->input("criteria.$id")['is_active'])]);
        }
        Setting::put('min_eligible_score', $d['min_eligible_score']);
        $n = $svc->refreshAll();
        Audit::log('scoring.updated', null, ['recalculated' => $n]);
        return back()->with('status', "Scoring rules saved. $n profiles recalculated.");
    }
}
