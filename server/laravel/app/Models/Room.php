<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Room model.
 *
 * SRP: Represents a room inside a gym.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property int|null    $gym_id
 * @property string|null $name
 * @property int|null    $capacity
 */
class Room extends Model
{
    use HasFactory;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'gym_id',
        'name',
        'capacity',
    ];

    /**
     * @return BelongsTo<Gym, Room>
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Returns all classes scheduled in this room.
     *
     * @return HasMany<GymClass>
     */
    public function classes(): HasMany
    {
        return $this->hasMany(GymClass::class);
    }
}
