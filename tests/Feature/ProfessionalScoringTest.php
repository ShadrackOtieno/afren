<?php
namespace Tests\Feature;

use App\Models\{Professional, RecruitmentRequest, ScoringCriterion};
use App\Services\{MatchingService, ScoringService};

class ProfessionalScoringTest extends AfrenTestCase
{
    public function test_registration_normalises_data_scores_and_blocks_duplicates(): void
    {
        $this->post('/register', $this->validRegistration())->assertRedirect('/my/profile');
        $p = Professional::first();
        $this->assertSame('712000111', $p->phone);
        $this->assertSame(['English', 'Swahili'], $p->languages);
        $this->assertGreaterThan(0, $p->profile_score);
        $this->assertCount(10, $p->score_breakdown);
        $this->assertNotNull($p->cv_path);
        $this->assertSame('professional', $p->user->role);
        $this->assertAuthenticatedAs($p->user);
        $this->post('/register', $this->validRegistration(['email' => 'other@t.test']))->assertSessionHasErrors('phone');
        $this->post('/register', $this->validRegistration(['phone' => '799999999']))->assertSessionHasErrors('email');
    }

    public function test_score_is_capped_at_100_and_weights_are_configurable(): void
    {
        $p = $this->pro(['years_experience' => 20, 'research_years' => 10, 'fieldwork_years' => 10, 'education_level' => 'phd', 'skills' => range(1, 8), 'tools' => range(1, 5), 'languages' => ['a', 'b', 'c']]);
        $this->assertEqualsWithDelta(100, $p->profile_score, 0.1);
        ScoringCriterion::where('key', 'cv')->update(['weight' => 40]); // others stay 10 => total weight 130
        $svc = app(ScoringService::class);
        $r = $svc->compute($p->fill(['cv_path' => null]));
        $this->assertLessThan(100, $r['score']);
        ScoringCriterion::where('key', 'cv')->update(['is_active' => false]);
        $this->assertEqualsWithDelta(100, $svc->compute($p)['score'], 0.1);
    }

    public function test_score_recalculates_on_admin_verification_change(): void
    {
        $p = $this->pro(['verification_status' => 'pending']); $before = $p->profile_score;
        $this->actingAs($this->staff('recruiter'))->put("/admin/professionals/{$p->id}", ['verification_status' => 'verified', 'is_active' => 1])->assertRedirect();
        $this->assertGreaterThan($before, $p->fresh()->profile_score);
    }

    public function test_matching_is_job_specific(): void
    {
        $this->post('/request-recruitment', $this->requestPayload(['languages' => 'Hausa', 'project_countries' => ['NG'], 'technical_skills' => '', 'tools' => '']));
        $req = RecruitmentRequest::first();
        $hausaNg = $this->pro(['country_code' => 'NG', 'languages' => ['Hausa', 'English']]);
        $swahiliKe = $this->pro(['country_code' => 'KE', 'languages' => ['Swahili'], 'profile_score' => 99]);
        $researcherOnly = $this->pro(['country_code' => 'NG', 'languages' => ['Hausa'], 'category' => 'researcher']);
        $ranked = app(MatchingService::class)->rank($req);
        $this->assertSame($hausaNg->id, $ranked[0]['professional']->id);
        $m = app(MatchingService::class);
        $this->assertGreaterThan($m->score($swahiliKe, $req)['score'], $m->score($hausaNg, $req)['score']);
        $this->assertFalse($m->score($researcherOnly, $req)['meets_basics']);
    }
}
