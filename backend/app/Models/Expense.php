<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'payee',
        'amount_due_cents',
        'amount_paid_cents',
        'performed_at',
    ];

    protected $casts = [
        'amount_due_cents' => 'integer',
        'amount_paid_cents' => 'integer',
        'performed_at' => 'datetime',
    ];
}
