<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User model – the central entity of the application.
 *
 * SRP: Represents a user and exposes its direct DB relationships.
 *      Business logic (booking policies, strike rules, etc.) belongs
 *      in dedicated service classes, not here.
 * DIP: Depends on Eloquent and Authenticatable abstractions.
 *
 * @property int         $id
 * @property string|null $username
 * @property string|null $email
 * @property string|null $password_hash
 * @property string|null $role            admin|manager|staff|client|user_online
 * @property string|null $full_name
 * @property string|null $dni
 * @property string|null $birth_date
 * @property string|null $profile_photo_url
 * @property int|null    $current_gym_id
 * @property int|null    $membership_plan_id
 * @property string|null $membership_status
 * @property int         $cancellation_strikes
 * @property bool        $is_blocked_from_booking
 * @property string      $created_at
 * @property string      $updated_at
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

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

    /** @var list<string> Never serialised to JSON. */
    protected $hidden = [
        'password_hash',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'birth_date'              => 'date',
        'is_blocked_from_booking' => 'boolean',
    ];

    /**
     * Returns the user's home gym.
     *
     * @return BelongsTo<Gym, User>
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class, 'current_gym_id');
    }

    /**
     * Returns the user's membership plan.
     *
     * @return BelongsTo<MembershipPlan, User>
     */
    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    /**
     * Returns all bookings made by this user.
     *
     * @return HasMany<Booking>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Returns all body-metric snapshots for this user.
     *
     * @return HasMany<BodyMetric>
     */
    public function bodyMetrics(): HasMany
    {
        return $this->hasMany(BodyMetric::class);
    }

    /**
     * Returns the settings record for this user.
     *
     * @return HasOne<Setting>
     */
    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    /**
     * Returns all active-routine links for this user.
     *
     * @return HasMany<UserActiveRoutine>
     */
    public function userActiveRoutines(): HasMany
    {
        return $this->hasMany(UserActiveRoutine::class);
    }

    /**
     * Returns the user's favourite entities.
     *
     * @return HasMany<UserFavorite>
     */
    public function userFavorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    /**
     * Returns the user's meal-schedule entries.
     *
     * @return HasMany<UserMealSchedule>
     */
    public function userMealSchedules(): HasMany
    {
        return $this->hasMany(UserMealSchedule::class);
    }

    /**
     * Returns partner links where this user is the primary account.
     *
     * @return HasMany<UserPartner>
     */
    public function primaryPartners(): HasMany
    {
        return $this->hasMany(UserPartner::class, 'primary_user_id');
    }

    /**
     * Returns partner links where this user is the linked account.
     *
     * @return HasMany<UserPartner>
     */
    public function partnerLinks(): HasMany
    {
        return $this->hasMany(UserPartner::class, 'partner_user_id');
    }

    /**
     * Returns attendance records for this user as staff.
     *
     * @return HasMany<StaffAttendance>
     */
    public function staffAttendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class, 'staff_id');
    }

    /**
     * Returns notifications sent by this user.
     *
     * @return HasMany<AppNotification>
     */
    public function sentNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class, 'sender_id');
    }

    /**
     * Returns gyms managed by this user.
     *
     * @return HasMany<Gym>
     */
    public function managedGyms(): HasMany
    {
        return $this->hasMany(Gym::class, 'manager_id');
    }

    /**
     * Returns classes where this user is the instructor.
     *
     * @return HasMany<GymClass>
     */
    public function instructedClasses(): HasMany
    {
        return $this->hasMany(GymClass::class, 'instructor_id');
    }

    /**
     * Returns routines created by this user.
     *
     * @return HasMany<Routine>
     */
    public function createdRoutines(): HasMany
    {
        return $this->hasMany(Routine::class, 'creator_id');
    }
}
