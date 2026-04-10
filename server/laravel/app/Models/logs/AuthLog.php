<?php

namespace App\Models\logs;

use Illuminate\Database\Eloquent\Model;

/**
 * Immutable authentication event log.
 *
 * SRP: Solely responsible for representing a single authentication event.
 *
 * NOTE: No Eloquent relationships. user_id may be null for failed attempts
 *       with unknown emails. The user account may be deleted after the event.
 *       Only created_at is used.
 *
 * @property int         $id
 * @property int|null    $user_id
 * @property string      $event_type
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property bool        $success
 * @property \Illuminate\Support\Carbon $created_at
 */
class AuthLog extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var string */
    protected $table = 'auth_logs';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'email_attempt',
        'event',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
