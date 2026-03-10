<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for the booking_history table.
 *
 * SRP: Solely responsible for representing a single booking status transition.
 * NOTE: Stores a full snapshot of booking_id, class_id and user_id at the
 *       time of the transition so the record is self-contained even if the
 *       original booking is deleted. changed_by_id is null for automatic
 *       system-driven transitions.
 */
class BookingHistory extends Model
{
    /** @var bool Disable automatic timestamp management. */
    public $timestamps = false;

    /** @var string Table name. */
    protected $table = 'booking_history';

    /** @var string[] Fillable fields. */
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

    /** @var array<string, string> Cast definitions. */
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
