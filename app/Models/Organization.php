<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Organization extends Model
{
    protected $guarded = [];
    public function user() { return $this->belongsTo(User::class); }
    public function subscriptions() { return $this->hasMany(Subscription::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
}
