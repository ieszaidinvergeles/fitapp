<?php

namespace App\Models\logs;

use Illuminate\Database\Eloquent\Model;

/**
 * Immutable audit log entry recording entity-level changes performed by actors.
 *
 * SRP: Solely responsible for representing a single auditable event snapshot.
 * OCP: New auditable entity types are added without modifying this model.
 *
 * NOTE: No Eloquent relationships. Stores actor and entity snapshots so the
 *       record remains valid even if the original rows are later deleted.
 *       Only created_at is relevant — updated_at is not used.
 *
 * @property int         $id
 * @property string      $entity_type
 * @property int|null    $entity_id
 * @property string      $action
 * @property int|null    $actor_id
 * @property string|null $actor_role
 * @property array|null  $old_values
 * @property array|null  $new_values
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon $created_at
 */
class AuditLog extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var string */
    protected $table = 'audit_logs';

    /** @var list<string> */
    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'actor_id',
        'actor_role',
        'old_values',
        'new_values',
        'ip_address',
        'created_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'old_values'  => 'array',
        'new_values'  => 'array',
        'created_at'  => 'datetime',
    ];
}
