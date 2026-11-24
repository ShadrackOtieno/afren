<?php
namespace App\Services;

use App\Models\Professional;
use App\Models\RecruitmentRequest;
use App\Support\Tags;

/**
 * Job-specific suitability (0-100) of a professional for ONE recruitment request.
 * Separate from the general profile score: it only reflects what this request asks for.
 */
class MatchingService
{
    public const WEIGHTS = ['category' => 15, 'location' => 15, 'languages' => 20, 'skills' => 20, 'tools' => 10, 'experience' => 10, 'education' => 5, 'availability' => 5];

    public function score(Professional $p, RecruitmentRequest $r): array
    {
        $parts = [];
        // Category
        $need = $r->personnel_type;
        $parts['category'] = ($need === 'both' || $p->category === 'both' || $p->category === $need) ? 1 : 0;
        // Location: lives in a project country (1), prefers one (.7), else willing to travel when travel needed (.4)
        $pc = array_map('strtoupper', $r->project_countries ?? []);
        $pref = array_map('strtoupper', $p->preferred_countries ?? []);
        $parts['location'] = in_array(strtoupper($p->country_code), $pc, true) ? 1
            : (array_intersect($pc, $pref) ? .7 : ($r->travel_required && $p->willing_to_travel ? .4 : 0));
        $parts['languages'] = $this->overlap($r->languages, $p->languages);
        $parts['skills'] = $this->overlap($r->technical_skills, $p->skills);
        $parts['tools'] = $this->overlap($r->tools, $p->tools);
        $parts['experience'] = $r->min_years > 0 ? min(1, $p->years_experience / $r->min_years) : 1;
        $reqEdu = config('afren.education_levels')[$r->min_education] ?? 0;
        $parts['education'] = $reqEdu > 0 ? ($p->education_rank >= $reqEdu ? 1 : ($p->education_rank == $reqEdu - 1 ? .5 : 0)) : 1;
        $parts['availability'] = ['available' => 1, 'limited' => .5, 'unavailable' => 0][$p->availability] ?? 0;

        $total = 0; $rows = [];
        foreach (self::WEIGHTS as $k => $w) {
            $pts = round($parts[$k] * $w, 2); $total += $pts;
            $rows[] = ['key' => $k, 'weight' => $w, 'fraction' => round($parts[$k], 2), 'points' => $pts];
        }
        // Hard gate: wrong professional category can never be a good match
        $meets = $parts['category'] == 1 && $p->availability !== 'unavailable';
        return ['score' => round($total, 2), 'breakdown' => $rows, 'meets_basics' => $meets];
    }

    /** Ranked candidates for a request. Returns array of ['professional','score','breakdown','meets_basics']. */
    public function rank(RecruitmentRequest $r, array $filters = [], int $limit = 100): array
    {
        $q = Professional::query()->where('is_active', true)->where('verification_status', '!=', 'rejected');
        if (! empty($filters['country'])) { $q->where('country_code', $filters['country']); }
        if (! empty($filters['verified'])) { $q->where('verification_status', 'verified'); }
        $out = [];
        foreach ($q->cursor() as $p) {
            $m = $this->score($p, $r);
            if (! empty($filters['min_score']) && $m['score'] < (float) $filters['min_score']) { continue; }
            $out[] = ['professional' => $p] + $m;
        }
        usort($out, fn ($a, $b) => [$b['meets_basics'], $b['score'], $b['professional']->profile_score] <=> [$a['meets_basics'], $a['score'], $a['professional']->profile_score]);
        return array_slice($out, 0, $limit);
    }

    private function overlap(?array $required, ?array $have): float
    {
        $required = Tags::lower($required);
        if (! $required) { return 1; }
        return count(array_intersect($required, Tags::lower($have))) / count($required);
    }
}
