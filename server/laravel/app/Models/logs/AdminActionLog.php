<?php

namespace App\Models\logs;

use Illuminate\Database\Eloquent\Model;

/**
 * Immutable log of privileged administrative actions.
 *
 * SRP: Solely responsible for representing a single admin-level action event.
 *
 * NOTE: No Eloquent relationships. actor_role is stored as a snapshot because
 *       the actor's role may change after the action. payload is a free JSON
 *       field for action-specific context. Only created_at is used.
 *
 * @property int         $id
 * @property int         $actor_id
 * @property string      $actor_role
 * @property string      $action
 * @property string|null $target_entity_type
 * @property int|null    $target_entity_id
 * @property int|null    $gym_id
 * @property array|null  $payload
 * @property \Illuminate\Support\Carbon $created_at
 */
class AdminActionLog extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var string */
    protected $table = 'admin_action_logs';

    /** @var list<string> */
    protected $fillable = [
        'actor_id',
        'actor_role',
        'action',
        'target_entity_type',
        'target_entity_id',
        'gym_id',
        'payload',
        'created_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'payload'    => 'array',
        'created_at' => 'datetime',
    ];
}
