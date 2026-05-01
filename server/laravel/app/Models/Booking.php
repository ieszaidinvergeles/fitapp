<?php

namespace App\Models;

use App\Models\logs\BookingHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Represents a user's reservation for a scheduled gym class.
 *
 * SRP: Encapsulates booking lifecycle — creation, cancellation, attendance, and no-show handling.
 * OCP: Each status transition is an isolated method. New statuses are added without altering existing ones.
 *
 * @property int                             $id
 * @property int                             $class_id
 * @property int                             $user_id
 * @property string                          $status
 * @property \Illuminate\Support\Carbon      $booked_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 *
 * @property-read \App\Models\GymClass $gymClass
 * @property-read \App\Models\User     $user
 */
class Booking extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'class_id',
        'user_id',
        'status',
        'booked_at',
        'cancelled_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'class_id'     => 'integer',
        'user_id'      => 'integer',
        'booked_at'    => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Relationship: the gym class this booking belongs to.
     *
     * @return BelongsTo
     */
    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClass::class, 'class_id');
    }

    /**
     * Relationship: the user who made this booking.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns whether this booking can still be cancelled.
     * A booking is cancellable if the class starts more than 2 hours from now.
     *
     * @return bool
     */
    public function isCancellable(): bool
    {
        return $this->gymClass->start_time->diffInHours(Carbon::now(), false) < -2;
    }

    /**
     * Cancels this booking, increments the user's cancellation strike,
     * and writes a BookingHistory entry.
     *
     * @return void
     */
    public function cancel(): void
    {
        $fromStatus = $this->status;

        $this->update([
            'status'       => 'cancelled',
            'cancelled_at' => Carbon::now(),
        ]);

        $this->user->incrementStrike();

        BookingHistory::create([
            'booking_id'    => $this->id,
            'class_id'      => $this->class_id,
            'user_id'       => $this->user_id,
            'from_status'   => $fromStatus,
            'to_status'     => 'cancelled',
            'changed_by_id' => $this->user_id,
            'reason'        => 'User cancellation',
        ]);
    }

    /**
     * Marks this booking as attended and writes a BookingHistory entry.
     *
     * @return void
     */
    public function markAttended(): void
    {
        $fromStatus = $this->status;

        $this->update(['status' => 'attended']);

        BookingHistory::create([
            'booking_id'    => $this->id,
            'class_id'      => $this->class_id,
            'user_id'       => $this->user_id,
            'from_status'   => $fromStatus,
            'to_status'     => 'attended',
            'changed_by_id' => $this->user_id,
            'reason'        => 'Attendance confirmed',
        ]);
    }

    /**
     * Marks this booking as no-show, increments the user's strike,
     * and writes a BookingHistory entry.
     *
     * @return void
     */
    public function markNoShow(): void
    {
        $fromStatus = $this->status;

        $this->update(['status' => 'no_show']);

        $this->user->incrementStrike();

        BookingHistory::create([
            'booking_id'    => $this->id,
            'class_id'      => $this->class_id,
            'user_id'       => $this->user_id,
            'from_status'   => $fromStatus,
            'to_status'     => 'no_show',
            'changed_by_id' => $this->user_id,
            'reason'        => 'No show',
        ]);
    }
}