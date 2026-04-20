<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'actor_user_id',
        'action',
        'entity_type',
        'entity_id',
        'metadata',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'entity_id' => 'integer',
        'actor_user_id' => 'integer',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public static function record(string $action, ?string $entityType = null, ?int $entityId = null, array $metadata = []): self
    {
        $actorUserId = auth()->user()?->id;

        return static::create([
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'entity_type' => $entityType ?? 'system',
            'entity_id' => $entityId,
            'metadata' => $metadata,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
