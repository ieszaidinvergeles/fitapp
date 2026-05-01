<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Represents a scheduled gym class session.
 *
 * SRP: Encapsulates class capacity, cancellation, attendance, and instructor conflict logic.
 * OCP: New class-level operations are added as methods without modifying the base structure.
 *
 * @property int                      $id
 * @property int                      $gym_id
 * @property int|null                 $activity_id
 * @property int|null                 $instructor_id
 * @property int|null                 $room_id
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property int|null                 $capacity_limit
 * @property bool                     $is_cancelled
 *
 * @property-read \App\Models\Gym                                                       $gym
 * @property-read \App\Models\Activity|null                                             $activity
 * @property-read \App\Models\User|null                                                 $instructor
 * @property-read \App\Models\Room|null                                                 $room
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking>   $bookings
 */
class GymClass extends Model
{
    /** @var string */
    protected $table = 'classes';

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'gym_id',
        'activity_id',
        'instructor_id',
        'room_id',
        'start_time',
        'end_time',
        'capacity_limit',
        'is_cancelled',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'gym_id'         => 'integer',
        'activity_id'    => 'integer',
        'instructor_id'  => 'integer',
        'room_id'        => 'integer',
        'start_time'     => 'datetime',
        'end_time'       => 'datetime',
        'capacity_limit' => 'integer',
        'is_cancelled'   => 'boolean',
    ];

    /**
     * Relationship: the gym hosting this class.
     *
     * @return BelongsTo
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Relationship: the activity type for this class.
     *
     * @return BelongsTo
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Relationship: the user designated as instructor for this class.
     *
     * @return BelongsTo
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Relationship: the room assigned to this class.
     *
     * @return BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Relationship: all booking records for this class.
     *
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'class_id');
    }

    /**
     * Returns the number of available spots in this class.
     *
     * @return int
     */
    public function availableSpots(): int
    {
        if ($this->capacity_limit === null) {
            return PHP_INT_MAX;
        }

        $active = $this->bookings()->where('status', 'active')->count();

        return max(0, $this->capacity_limit - $active);
    }

    /**
     * Returns whether all available spots in this class are filled.
     *
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->availableSpots() <= 0;
    }

    /**
     * Returns whether a specific user has an active booking for this class.
     *
     * @param  int  $userId
     * @return bool
     */
    public function isUserBooked(int $userId): bool
    {
        return $this->bookings()
                    ->where('user_id', $userId)
                    ->where('status', 'active')
                    ->exists();
    }

    /**
     * Cancels the class and all its active bookings.
     * Returns true on success, false on failure.
     *
     * @return bool
     */
    public function cancel(): bool
    {
        $this->bookings()->where('status', 'active')->update(['status' => 'cancelled']);
        return $this->update(['is_cancelled' => true]);
    }

    /**
     * Returns the occupancy percentage of this class relative to its capacity.
     *
     * @return float
     */
    public function occupancyPercentage(): float
    {
        if (!$this->capacity_limit) {
            return 0.0;
        }

        $active = $this->bookings()->where('status', 'active')->count();

        return round(($active / $this->capacity_limit) * 100, 2);
    }

    /**
     * Marks all active bookings for this class as attended.
     *
     * @return void
     */
    public function markAllAttended(): void
    {
        $this->bookings()->where('status', 'active')->update(['status' => 'attended']);
    }

    /**
     * Returns whether a given instructor has a scheduling conflict for the given time range.
     *
     * @param  int     $instructorId
     * @param  Carbon  $start
     * @param  Carbon  $end
     * @return bool
     */
    public function hasInstructorConflict(int $instructorId, Carbon $start, Carbon $end): bool
    {
        return self::where('instructor_id', $instructorId)
                   ->where('is_cancelled', false)
                   ->where(function ($q) use ($start, $end) {
                       $q->whereBetween('start_time', [$start, $end])
                         ->orWhereBetween('end_time', [$start, $end])
                         ->orWhere(function ($q2) use ($start, $end) {
                             $q2->where('start_time', '<=', $start)
                                ->where('end_time', '>=', $end);
                         });
                   })
                   ->exists();
    }
}