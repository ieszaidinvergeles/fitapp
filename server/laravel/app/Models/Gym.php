<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Represents a physical gym location managed within the system.
 *
 * SRP: Encapsulates gym identity, manager assignment, and all location-scoped relationships.
 * OCP: New gym-level features are added as relationships or methods without altering existing logic.
 *
 * @property int         $id
 * @property string      $name
 * @property int|null    $manager_id
 * @property string      $address
 * @property string      $city
 * @property string|null $location_coords
 * @property string      $phone
 * @property string|null $logo_url
 *
 * @property-read \App\Models\User|null                                                   $manager
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>         $users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Room>         $rooms
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GymClass>     $classes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Equipment>    $equipment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StaffAttendance> $staffAttendances
 */
class Gym extends Model
{
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
        'logo_url',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'manager_id' => 'integer',
    ];

    /**
     * Relationship: the user designated as manager of this gym.
     *
     * @return BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Relationship: all users currently assigned to this gym.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'current_gym_id');
    }

    /**
     * Relationship: all rooms in this gym.
     *
     * @return HasMany
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Relationship: all class sessions scheduled at this gym.
     *
     * @return HasMany
     */
    public function classes(): HasMany
    {
        return $this->hasMany(GymClass::class);
    }

    /**
     * Relationship: all equipment stocked at this gym, with inventory pivot data.
     *
     * @return BelongsToMany
     */
    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'gym_inventory')
                    ->withPivot('quantity', 'status');
    }

    /**
     * Relationship: all notifications targeting this gym.
     *
     * @return HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'related_gym_id');
    }

    /**
     * Relationship: all staff attendance records for this gym.
     *
     * @return HasMany
     */
    public function staffAttendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    /**
     * Returns whether this gym currently has an assigned manager.
     *
     * @return bool
     */
    public function hasManager(): bool
    {
        return $this->manager_id !== null;
    }

    /**
     * Assigns a user as the manager of this gym and persists the change.
     *
     * @param  int  $userId
     * @return bool
     */
    public function assignManager(int $userId): bool
    {
        return $this->update(['manager_id' => $userId]);
    }

    /**
     * Returns all active (non-cancelled, future) classes for this gym.
     *
     * @return HasMany
     */
    public function activeClasses(): HasMany
    {
        return $this->classes()
                    ->where('is_cancelled', false)
                    ->where('start_time', '>', Carbon::now());
    }

    /**
     * Returns all assistant, staff, and manager users currently assigned to this gym.
     *
     * @return HasMany
     */
    public function staffMembers(): HasMany
    {
        return $this->users()
                    ->whereIn('role', ['assistant', 'staff', 'manager']);
    }
}
