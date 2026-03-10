<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for the audit_logs table.
 *
 * SRP: Solely responsible for representing a single audit log entry.
 * OCP: New auditable entity types can be added without modifying this model.
 * NOTE: No relationships defined — this model stores snapshots of data
 *       that may no longer exist in the main DB. Timestamps are disabled
 *       because only created_at is relevant and is set via useCurrent().
 */
class AuditLog extends Model
{
    /** @var bool Disable automatic timestamp management. */
    public $timestamps = false;

    /** @var string Table name. */
    protected $table = 'audit_logs';

    /** @var string[] Fillable fields. */
    protected $fillable = [
        'actor_id',
        'actor_role',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /** @var array<string, string> Cast definitions. */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];
}
