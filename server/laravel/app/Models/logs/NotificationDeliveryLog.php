<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for the notification_delivery_logs table.
 *
 * SRP: Solely responsible for representing a single notification
 *      delivery event per recipient and channel.
 * NOTE: notification_id and recipient_id are stored as plain integers
 *       with no FK constraints. The notification may be deleted from
 *       the main DB while the delivery record must be preserved.
 */
class NotificationDeliveryLog extends Model
{
    /** @var bool Disable automatic timestamp management. */
    public $timestamps = false;

    /** @var string Table name. */
    protected $table = 'notification_delivery_logs';

    /** @var string[] Fillable fields. */
    protected $fillable = [
        'notification_id',
        'recipient_id',
        'channel',
        'status',
        'delivered_at',
        'read_at',
        'created_at',
    ];

    /** @var array<string, string> Cast definitions. */
    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at'      => 'datetime',
        'created_at'   => 'datetime',
    ];
}
