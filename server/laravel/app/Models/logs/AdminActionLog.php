<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for the admin_action_logs table.
 *
 * SRP: Solely responsible for representing a single privileged action event.
 * NOTE: actor_role is stored as a snapshot because the actor's role may
 *       change after the action was performed. payload is a free JSON field
 *       to accommodate action-specific context without requiring new columns.
 */
class AdminActionLog extends Model
{
    /** @var bool Disable automatic timestamp management. */
    public $timestamps = false;

    /** @var string Table name. */
    protected $table = 'admin_action_logs';

    /** @var string[] Fillable fields. */
    protected $fillable = [
        'actor_id',
        'actor_role',
        'action',
        'target_entity_type',
        'target_entity_id',
        'payload',
        'ip_address',
        'created_at',
    ];

    /** @var array<string, string> Cast definitions. */
    protected $casts = [
        'payload'    => 'array',
        'created_at' => 'datetime',
    ];
}
