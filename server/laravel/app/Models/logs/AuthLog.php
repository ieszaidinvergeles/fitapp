<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for the auth_logs table.
 *
 * SRP: Solely responsible for representing a single authentication event.
 * NOTE: user_id may be null for failed login attempts with unknown emails.
 *       No relationships to User — the user account may be deleted after
 *       the event was recorded.
 */
class AuthLog extends Model
{
    /** @var bool Disable automatic timestamp management. */
    public $timestamps = false;

    /** @var string Table name. */
    protected $table = 'auth_logs';

    /** @var string[] Fillable fields. */
    protected $fillable = [
        'user_id',
        'email_attempt',
        'event',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /** @var array<string, string> Cast definitions. */
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
