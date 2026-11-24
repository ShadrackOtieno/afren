<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Professional extends Model
{
    protected $guarded = [];
    protected $casts = [
        'skills' => 'array', 'tools' => 'array', 'languages' => 'array', 'preferred_countries' => 'array',
        'documents' => 'array', 'score_breakdown' => 'array', 'willing_to_travel' => 'boolean',
        'is_active' => 'boolean', 'verified_at' => 'datetime', 'consent_at' => 'datetime', 'profile_score' => 'float',
    ];
    public function user() { return $this->belongsTo(User::class); }
    public function country() { return $this->belongsTo(Country::class, 'country_code', 'iso2'); }
    public function shortlists() { return $this->hasMany(Shortlist::class); }
    public function getEducationRankAttribute(): int { return config('afren.education_levels')[$this->education_level] ?? 0; }
    public function isEligible(): bool
    {
        return $this->is_active && $this->verification_status !== 'rejected'
            && $this->profile_score >= (float) Setting::get('min_eligible_score', 0);
    }
}
