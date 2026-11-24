<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password', 'role', 'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function professional() { return $this->hasOne(Professional::class); }
    public function organization() { return $this->hasOne(Organization::class); }
    public function isStaff(): bool { return in_array($this->role, ['super_admin', 'admin', 'recruiter', 'finance'], true); }

    /** Where this user lands after signing in. */
    public function homeRoute(): string
    {
        return match (true) { $this->isStaff() => route('admin.dashboard'), $this->role === 'professional' => route('profile.edit'), default => route('portal.show') };
    }
}
