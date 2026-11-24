<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Shortlist extends Model
{
    protected $guarded = [];
    public function professional() { return $this->belongsTo(Professional::class); }
    public function request() { return $this->belongsTo(RecruitmentRequest::class, 'recruitment_request_id'); }
}
