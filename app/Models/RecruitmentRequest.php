<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RecruitmentRequest extends Model
{
    protected $guarded = [];
    protected $casts = [
        'project_countries' => 'array', 'technical_skills' => 'array', 'languages' => 'array', 'tools' => 'array',
        'travel_required' => 'boolean', 'start_date' => 'date', 'end_date' => 'date', 'recruitment_deadline' => 'date',
        'consent_at' => 'datetime',
    ];
    public function subscription() { return $this->belongsTo(Subscription::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function shortlists() { return $this->hasMany(Shortlist::class)->orderByDesc('job_score'); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public static function newReference(): string
    {
        do { $ref = 'REQ-'.now()->format('Y').'-'.strtoupper(\Illuminate\Support\Str::random(6)); }
        while (static::where('reference', $ref)->exists());
        return $ref;
    }
}
