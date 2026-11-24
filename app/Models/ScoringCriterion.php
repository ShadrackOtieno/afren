<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ScoringCriterion extends Model
{
    public $timestamps = false; protected $table = 'scoring_criteria'; protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];
}
