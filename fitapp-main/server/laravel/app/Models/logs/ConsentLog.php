<?php

namespace App\Models\logs;

use Illuminate\Database\Eloquent\Model;

/**
 * Immutable GDPR consent event log.
 *
 * SRP: Solely responsible for representing a single consent grant or revocation.
 *
 * NOTE: No Eloquent relationships. This is the most legally sensitive log model.
 *       Records must never be modified or deleted — they serve as legal evidence.
 *       Only created_at is used.
 *
 * @property int         $id
 * @property int         $user_id
 * @property string      $consent_type
 * @property string      $action
 * @property string|null $version
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon $created_at
 */
class ConsentLog extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var string */
    protected $table = 'consent_logs';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'consent_type',
        'action',
        'version',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
