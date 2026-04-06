<?php

namespace App\Models\logs;

use Illuminate\Database\Eloquent\Model;

/**
 * Immutable record of a booking status transition.
 *
 * SRP: Solely responsible for representing a single status change event for a booking.
 *
 * NOTE: No Eloquent relationships. Stores booking_id, class_id and user_id as
 *       a snapshot at the time of transition. Records are self-contained even
 *       if the original booking is hard-deleted. Only created_at is used.
 *
 * @property int         $id
 * @property int         $booking_id
 * @property int         $class_id
 * @property int         $user_id
 * @property string      $from_status
 * @property string      $to_status
 * @property int|null    $changed_by_id
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon $created_at
 */
class BookingHistory extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var string */
    protected $table = 'booking_history';

    /** @var list<string> */
    protected $fillable = [
        'booking_id',
        'class_id',
        'user_id',
        'from_status',
        'to_status',
        'changed_by_id',
        'reason',
        'created_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'booking_id'    => 'integer',
        'class_id'      => 'integer',
        'user_id'       => 'integer',
        'changed_by_id' => 'integer',
        'created_at'    => 'datetime',
    ];
}
