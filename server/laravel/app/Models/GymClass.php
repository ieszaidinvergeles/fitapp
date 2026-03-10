<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * GymClass model.
 *
 * Named GymClass because 'class' is a reserved PHP keyword.
 * Maps to the 'classes' table.
 *
 * SRP: Represents a scheduled gym class instance.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property int|null    $gym_id
 * @property int|null    $activity_id
 * @property int|null    $instructor_id
 * @property int|null    $room_id
 * @property string      $start_time
 * @property string|null $end_time
 * @property int|null    $capacity_limit
 * @property bool        $is_cancelled
 */
class GymClass extends Model
{
    use HasFactory;

    /** @var string Explicit table name because model name differs from convention. */
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

    /** @var array<string,string> */
    protected $casts = [
        'start_time'   => 'datetime',
        'end_time'     => 'datetime',
        'is_cancelled' => 'boolean',
    ];

    /**
     * @return BelongsTo<Gym, GymClass>
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * @return BelongsTo<Activity, GymClass>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * @return BelongsTo<User, GymClass>
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * @return BelongsTo<Room, GymClass>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Returns all bookings for this class.
     *
     * @return HasMany<Booking>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'class_id');
    }
}
