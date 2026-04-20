<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'is_active',
        'first_name',
        'last_name',
        'job_title',
        'work_schedule',
        'email',
        'password',
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
            'work_schedule' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email !== null && $this->email_verified_at !== null;
    }

    public function markEmailAsUnverified(): void
    {
        $this->forceFill(['email_verified_at' => null])->save();
    }

    public function markEmailAsVerified(): void
    {
        $this->forceFill(['email_verified_at' => now()])->save();
    }

    public function emailVerificationHash(): string
    {
        $email = mb_strtolower(trim((string) ($this->email ?? '')));
        return hash('sha256', $email);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function roleName(): string
    {
        $name = $this->role?->name;
        return is_string($name) ? strtolower(trim($name)) : '';
    }

    public function isAdmin(): bool
    {
        return $this->roleName() === 'admin';
    }

    public function isReceptionist(): bool
    {
        return $this->roleName() === 'recepcion';
    }

    /** @return 'admin'|'reception'|'other' */
    public function accessLevel(): string
    {
        if ($this->isAdmin()) return 'admin';
        if ($this->isReceptionist()) return 'reception';
        return 'other';
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
