<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Subscription extends Model
{
    protected $guarded = [];
    protected $casts = ['starts_at' => 'datetime', 'renews_at' => 'datetime', 'ends_at' => 'datetime', 'cancelled_at' => 'datetime', 'cancel_at_period_end' => 'boolean'];
    public function organization() { return $this->belongsTo(Organization::class); }
    public function package() { return $this->belongsTo(Package::class); }
    public function pendingPackage() { return $this->belongsTo(Package::class, 'pending_package_id'); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function events() { return $this->hasMany(SubscriptionEvent::class)->latest('id'); }
    public function requests() { return $this->hasMany(RecruitmentRequest::class); }
    public function isActive(): bool { return in_array($this->status, ['active', 'past_due'], true); }
    /** Requests submitted in the current billing period (for package limits). */
    public function requestsThisPeriod(): int
    {
        $from = $this->renews_at ? $this->renews_at->copy()->subMonth() : ($this->starts_at ?? now()->startOfMonth());
        return $this->requests()->where('created_at', '>=', $from)->count();
    }
}
