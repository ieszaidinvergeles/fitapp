<?php

namespace App\Models\logs;

use Illuminate\Database\Eloquent\Model;

/**
 * Immutable notification delivery event log per recipient and channel.
 *
 * SRP: Solely responsible for tracking whether a notification reached its recipient.
 *
 * NOTE: No Eloquent relationships. notification_id and recipient_id are stored
 *       as plain integers. The notification may be deleted from the main DB
 *       while delivery records must be preserved. Only created_at is managed.
 *
 * @property int                             $id
 * @property int                             $notification_id
 * @property int                             $recipient_id
 * @property string                          $channel
 * @property string                          $status
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon      $created_at
 */
class NotificationDeliveryLog extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var string */
    protected $table = 'notification_delivery_logs';

    /** @var list<string> */
    protected $fillable = [
        'notification_id',
        'recipient_id',
        'channel',
        'status',
        'delivered_at',
        'read_at',
        'created_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at'      => 'datetime',
        'created_at'   => 'datetime',
    ];
}
