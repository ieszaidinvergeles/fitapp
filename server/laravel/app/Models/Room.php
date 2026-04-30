<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Represents a physical room within a gym where classes are held.
 *
 * SRP: Encapsulates room configuration and time-conflict resolution for class scheduling.
 * OCP: Conflict detection logic is self-contained and extensible per room.
 *
 * @property int         $id
 * @property int         $gym_id
 * @property string      $name
 * @property int         $capacity
 * @property string|null $image_url
 *
 * @property-read \App\Models\Gym                                                      $gym
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GymClass> $gymClasses
 */
class Room extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'gym_id',
        'name',
        'capacity',
        'image_url',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'gym_id'   => 'integer',
        'capacity' => 'integer',
    ];

    /**
     * Relationship: the gym this room belongs to.
     *
     * @return BelongsTo
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Relationship: all class sessions scheduled in this room.
     *
     * @return HasMany
     */
    public function gymClasses(): HasMany
    {
        return $this->hasMany(GymClass::class, 'room_id');
    }

    /**
     * Returns whether there is a scheduling conflict for the given time range in this room.
     * Optionally excludes a specific class ID from the conflict check.
     *
     * @param  Carbon    $start
     * @param  Carbon    $end
     * @param  int|null  $excludeClassId
     * @return bool
     */
    public function hasConflict(Carbon $start, Carbon $end, ?int $excludeClassId = null): bool
    {
        return $this->gymClasses()
                    ->where('is_cancelled', false)
                    ->when($excludeClassId, fn ($q) => $q->where('id', '!=', $excludeClassId))
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