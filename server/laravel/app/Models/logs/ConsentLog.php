<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for the consent_logs table.
 *
 * SRP: Solely responsible for representing a single GDPR consent event.
 * NOTE: This is the most legally sensitive log model. Every record
 *       represents evidence of user consent or revocation and must
 *       never be modified or deleted.
 */
class ConsentLog extends Model
{
    /** @var bool Disable automatic timestamp management. */
    public $timestamps = false;

    /** @var string Table name. */
    protected $table = 'consent_logs';

    /** @var string[] Fillable fields. */
    protected $fillable = [
        'user_id',
        'consent_type',
        'action',
        'version',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /** @var array<string, string> Cast definitions. */
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
