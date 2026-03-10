
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a registered user in the system.
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string|null $password_hash
 * @property string $role
 * @property string|null $full_name
 * @property string $dni
 * @property \Illuminate\Support\Carbon $birth_date
 * @property string|null $profile_photo_url
 * @property int $current_gym_id
 * @property int $membership_plan_id
 * @property string $membership_status
 * @property int $cancellation_strikes
 * @property bool $is_blocked_from_booking
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Gym $currentGym
 * @property-read \App\Models\MembershipPlan $membershipPlan
 * @property-read \App\Models\Setting|null $settings
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BodyMetric> $bodyMetrics
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Routine> $createdRoutines
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $sentNotifications
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StaffAttendance> $staffAttendances
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserMealSchedule> $mealSchedules
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserFavorite> $favorites
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Routine> $activeRoutines
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $partners
 */

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role',
        'full_name',
        'dni',
        'birth_date',
        'profile_photo_url',
        'current_gym_id',
        'membership_plan_id',
        'membership_status',
        'cancellation_strikes',
        'is_blocked_from_booking',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'birth_date' => 'date',
        'cancellation_strikes' => 'integer',
        'is_blocked_from_booking' => 'boolean',
        'password_hash' => 'hashed', 
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function currentGym(): BelongsTo
    {
        return $this->belongsTo(Gym::class, 'current_gym_id');
    }

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    public function bodyMetrics(): HasMany
    {
        return $this->hasMany(BodyMetric::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function createdRoutines(): HasMany
    {
        return $this->hasMany(Routine::class, 'creator_id');
    }

    public function sentNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'sender_id');
    }

    public function staffAttendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class, 'staff_id');
    }

    public function mealSchedules(): HasMany
    {
        return $this->hasMany(UserMealSchedule::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    public function activeRoutines(): BelongsToMany
    {
        return $this->belongsToMany(Routine::class, 'user_active_routines')
                    ->withPivot('is_active', 'start_date');
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_partners', 'primary_user_id', 'partner_user_id')
                    ->withPivot('linked_at');
    }
}