<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Invoice extends Model
{
    protected $guarded = [];
    protected $casts = ['issue_date' => 'date', 'due_date' => 'date', 'period_start' => 'date', 'period_end' => 'date', 'sent_at' => 'datetime'];
    public function items() { return $this->hasMany(InvoiceItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function organization() { return $this->belongsTo(Organization::class); }
    public function subscription() { return $this->belongsTo(Subscription::class); }
    public function request() { return $this->belongsTo(RecruitmentRequest::class, 'recruitment_request_id'); }
    public function paidAmount(): float { return (float) $this->payments()->sum('amount'); }
    public function balance(): float { return max(0, round((float) $this->total - $this->paidAmount(), 2)); }
    public function isOverdue(): bool { return $this->status === 'sent' && $this->due_date && $this->due_date->isPast(); }
}
