<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model
{
    protected $guarded = []; protected $casts = ['paid_on' => 'date'];
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function recorder() { return $this->belongsTo(User::class, 'recorded_by'); }
}
