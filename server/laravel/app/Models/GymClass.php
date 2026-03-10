
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a scheduled gym class session.
 *
 * @property int $id
 * @property int $gym_id
 * @property int $activity_id
 * @property int $instructor_id
 * @property int $room_id
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property int|null $capacity_limit
 * @property bool $is_cancelled
 *
 * @property-read \App\Models\Gym $gym
 * @property-read \App\Models\Activity $activity
 * @property-read \App\Models\User $instructor
 * @property-read \App\Models\Room $room
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 */

class GymClass extends Model
{
    protected $table = 'classes';
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

    /** @var array<string,string> */
    protected $casts = [
        'gym_id' => 'integer',
        'activity_id' => 'integer',
        'instructor_id' => 'integer',
        'room_id' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'capacity_limit' => 'integer',
        'is_cancelled' => 'boolean',
    ];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'class_id');
    }
}