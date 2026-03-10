
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a physical gym location.
 *
 * @property int $id
 * @property string $name
 * @property int $manager_id
 * @property string $address
 * @property string $city
 * @property string|null $location_coords
 * @property string $phone
 *
 * @property-read \App\Models\User $manager
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Room> $rooms
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GymClass> $classes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Equipment> $equipment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StaffAttendance> $staffAttendances
 */

class Gym extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'manager_id',
        'address',
        'city',
        'location_coords',
        'phone',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'manager_id' => 'integer',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'current_gym_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(GymClass::class);
    }

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'gym_inventory')
                    ->withPivot('quantity', 'status');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'related_gym_id');
    }

    public function staffAttendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }
}