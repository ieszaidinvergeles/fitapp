<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Gym model.
 *
 * SRP: Represents a physical gym location and its relationships.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property string|null $name
 * @property int|null    $manager_id
 * @property string|null $address
 * @property string|null $city
 * @property string|null $location_coords
 * @property string|null $phone
 */
class Gym extends Model
{
    use HasFactory;

    /** @var bool */
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

    /**
     * Returns the user who manages this gym.
     *
     * @return BelongsTo<User, Gym>
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Returns all rooms inside this gym.
     *
     * @return HasMany<Room>
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Returns all scheduled classes at this gym.
     *
     * @return HasMany<GymClass>
     */
    public function classes(): HasMany
    {
        return $this->hasMany(GymClass::class);
    }

    /**
     * Returns the equipment inventory for this gym.
     *
     * @return HasMany<GymInventory>
     */
    public function gymInventory(): HasMany
    {
        return $this->hasMany(GymInventory::class);
    }

    /**
     * Returns staff attendance records for this gym.
     *
     * @return HasMany<StaffAttendance>
     */
    public function staffAttendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    /**
     * Returns notifications targeted at this gym.
     *
     * @return HasMany<AppNotification>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class, 'related_gym_id');
    }
}
