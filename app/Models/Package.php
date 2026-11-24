<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Package extends Model
{
    protected $guarded = [];
    protected $casts = ['features' => 'array', 'is_active' => 'boolean', 'is_featured' => 'boolean', 'price' => 'decimal:2'];
    public function subscriptions() { return $this->hasMany(Subscription::class); }
}
