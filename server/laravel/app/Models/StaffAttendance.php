<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a clock-in/clock-out attendance record for a staff member at a gym.
 *
 * SRP: Encapsulates staff working hours tracking, filtering, and missing clock-out detection.
 * OCP: New time range filters are added as scopes without modifying core attendance logic.
 *
 * @property int                             $id
 * @property int|null                        $staff_id
 * @property int|null                        $gym_id
 * @property \Illuminate\Support\Carbon      $clock_in
 * @property \Illuminate\Support\Carbon|null $clock_out
 * @property \Illuminate\Support\Carbon      $date
 *
 * @property-read \App\Models\User|null $staff
 * @property-read \App\Models\Gym|null  $gym
 */
class StaffAttendance extends Model
{
    /** @var string */
    protected $table = 'staff_attendance';

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'staff_id',
        'gym_id',
        'clock_in',
        'clock_out',
        'date',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'staff_id'  => 'integer',
        'gym_id'    => 'integer',
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
        'date'      => 'date',
    ];

    /**
     * Relationship: the staff user this record belongs to.
     *
     * @return BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Relationship: the gym where the staff member worked.
     *
     * @return BelongsTo
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Returns the total hours worked in this attendance record.
     * Returns null if the staff member has not yet clocked out.
     *
     * @return float|null
     */
    public function hoursWorked(): ?float
    {
        if ($this->clock_out === null) {
            return null;
        }

        return round($this->clock_in->diffInMinutes($this->clock_out) / 60, 2);
    }

    /**
     * Scope: filters attendance records for a specific staff member.
     *
     * @param  Builder  $query
     * @param  int      $staffId
     * @return Builder
     */
    public function scopeForStaff(Builder $query, int $staffId): Builder
    {
        return $query->where('staff_id', $staffId);
    }

    /**
     * Scope: filters attendance records within a date range.
     *
     * @param  Builder  $query
     * @param  string   $from
     * @param  string   $to
     * @return Builder
     */
    public function scopeForDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    /**
     * Returns whether this attendance record is missing a clock-out time.
     *
     * @return bool
     */
    public function hasMissingClockOut(): bool
    {
        return $this->clock_out === null;
    }
}