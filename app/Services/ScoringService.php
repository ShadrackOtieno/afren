<?php
namespace App\Services;

use App\Models\Professional;
use App\Models\ScoringCriterion;

/**
 * Profile-completeness / general quality score (default max 100).
 * Each criterion yields a 0..1 fraction multiplied by its admin-configured weight,
 * then the total is normalised to $max so weights can be changed freely.
 */
class ScoringService
{
    public const DEFAULTS = [
        ['degree', 'Relevant degree or qualification', 'Highest education level attained', 10],
        ['cv', 'CV available', 'CV uploaded to the profile', 10],
        ['relevant_experience', 'Relevant professional experience', 'Depth of the experience summary', 10],
        ['years_experience', 'Years of experience', 'Total years (10+ = full marks)', 10],
        ['research_experience', 'Research experience', 'Years in research roles (5+ = full marks)', 10],
        ['fieldwork_experience', 'Field data collection experience', 'Years of fieldwork (5+ = full marks)', 10],
        ['technical_skills', 'Technical skills', 'Number of skills listed (6+ = full marks)', 10],
        ['tool_proficiency', 'Data collection tools', 'Tools/software listed (4+ = full marks)', 10],
        ['language_proficiency', 'Language proficiency', 'Languages listed (3+ = full marks)', 10],
        ['verification', 'Certifications / profile verification', 'Verified by AFREN staff', 10],
    ];

    public function fraction(string $key, Professional $p): float
    {
        $f = match ($key) {
            'degree' => [0, .3, .5, .7, .9, 1][$p->education_rank] ?? 0,
            'cv' => $p->cv_path ? 1 : 0,
            'relevant_experience' => (($n = mb_strlen(trim((string) $p->experience_summary))) >= 200) ? 1 : ($n >= 50 ? .5 : 0),
            'years_experience' => $p->years_experience / 10,
            'research_experience' => $p->research_years / 5,
            'fieldwork_experience' => $p->fieldwork_years / 5,
            'technical_skills' => count($p->skills ?? []) / 6,
            'tool_proficiency' => count($p->tools ?? []) / 4,
            'language_proficiency' => count($p->languages ?? []) / 3,
            'verification' => $p->verification_status === 'verified' ? 1 : 0,
            default => 0,
        };
        return max(0, min(1, (float) $f));
    }

    public function compute(Professional $p, float $max = 100): array
    {
        $criteria = ScoringCriterion::where('is_active', true)->orderBy('sort')->get();
        $totalWeight = max(1, $criteria->sum('weight'));
        $rows = []; $score = 0;
        foreach ($criteria as $c) {
            $fr = $this->fraction($c->key, $p);
            $pts = round($fr * $c->weight / $totalWeight * $max, 2);
            $score += $pts;
            $rows[] = ['key' => $c->key, 'label' => $c->label, 'weight' => $c->weight, 'fraction' => round($fr, 2), 'points' => $pts,
                       'max_points' => round($c->weight / $totalWeight * $max, 2)];
        }
        return ['score' => round($score, 2), 'breakdown' => $rows];
    }

    /** Recalculate and persist. Call whenever profile data changes. */
    public function refresh(Professional $p): Professional
    {
        $r = $this->compute($p);
        $p->forceFill(['profile_score' => $r['score'], 'score_breakdown' => $r['breakdown']])->save();
        return $p;
    }

    public function refreshAll(): int
    {
        $n = 0;
        Professional::chunkById(200, function ($chunk) use (&$n) { foreach ($chunk as $p) { $this->refresh($p); $n++; } });
        return $n;
    }
}
