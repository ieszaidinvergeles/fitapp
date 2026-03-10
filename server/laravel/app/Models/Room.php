
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a room or specific area within a gym.
 *
 * @property int $id
 * @property int $gym_id
 * @property string $name
 * @property int $capacity
 *
 * @property-read \App\Models\Gym $gym
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GymClass> $classes
 */

class Room extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'gym_id',
        'name',
        'capacity',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'gym_id' => 'integer',
        'capacity' => 'integer',
    ];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(GymClass::class);
    }
}