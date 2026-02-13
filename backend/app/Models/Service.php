<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_minutes',
        'price_cents',
        'is_active',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'price_cents' => 'integer',
        'is_active' => 'boolean',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
