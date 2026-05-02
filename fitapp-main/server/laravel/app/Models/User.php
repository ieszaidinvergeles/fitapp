<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * Represents a registered user in the system.
 *
 * SRP: Encapsulates user state, role resolution, and Eloquent relationships.
 * OCP: Role helper methods can be extended without modifying core auth logic.
 *
 * @property int                             $id
 * @property string                          $username
 * @property string                          $email
 * @property string|null                     $email_verified_at
 * @property string|null                     $password_hash
 * @property string                          $role
 * @property string|null                     $full_name
 * @property string                          $dni
 * @property \Illuminate\Support\Carbon    $birth_date
 * @property string|null                     $profile_photo_url
 * @property int|null                        $current_gym_id
 * @property int|null                        $membership_plan_id
 * @property string                          $membership_status
 * @property int                             $cancellation_strikes
 * @property bool                            $is_blocked_from_booking
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Gym|null                                              $currentGym
 * @property-read \App\Models\MembershipPlan|null                                   $membershipPlan
 * @property-read \App\Models\Setting|null                                          $settings
 * @property-read \App\Models\BodyMetric|null                                       $latestBodyMetric
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BodyMetric>       $bodyMetrics
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking>          $bookings
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Routine>          $createdRoutines
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification>  $sentNotifications
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StaffAttendance>  $staffAttendances
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserMealSchedule> $mealSchedules
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserFavorite>     $favorites
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Routine>          $activeRoutines
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>             $partners
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'username',
        'email',
        'email_verified_at',
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

    /** @var array<string, string> */
    protected $casts = [
        'birth_date'              => 'date',
        'email_verified_at'       => 'datetime',
        'cancellation_strikes'    => 'integer',
        'is_blocked_from_booking' => 'boolean',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Overrides the default password column name used by Laravel's auth system.
     *
     * DIP: Decouples the framework's auth contract from the custom column name.
     *
     * @return string
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /**
     * Returns whether the user holds the admin role.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Returns whether the user holds an advanced role — admin, manager, or staff.
     *
     * @return bool
     */
    public function isAdvanced(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'staff'], true);
    }

    /**
     * Returns whether the user holds the assistant role.
     *
     * @return bool
     */
    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    /**
     * Returns whether the user holds the manager role.
     *
     * @return bool
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Returns whether the user holds the staff role.
     *
     * @return bool
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Returns whether the user may access the staff portal.
     *
     * @return bool
     */
    public function canAccessStaffPortal(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'assistant', 'staff'], true);
    }

    /**
     * Returns whether the user may perform front-desk member operations.
     *
     * @return bool
     */
    public function canManageMembers(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'assistant'], true);
    }

    /**
     * Returns whether the user may operate booking and attendance flows.
     *
     * @return bool
     */
    public function canManageOperations(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'assistant', 'staff'], true);
    }

    /**
     * Returns whether the user holds a regular user role — client or user_online.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return in_array($this->role, ['client', 'user_online'], true);
    }

    /**
     * Returns whether the user's membership is currently active.
     *
     * @return bool
     */
    public function hasMembershipActive(): bool
    {
        return $this->membership_status === 'active';
    }

    /**
     * Returns whether the user is blocked from making bookings.
     *
     * @return bool
     */
    public function isBlockedFromBooking(): bool
    {
        return $this->is_blocked_from_booking;
    }

    /**
     * Increments the user's cancellation strike counter and blocks if threshold is reached.
     *
     * @return void
     */
    public function incrementStrike(): void
    {
        $this->increment('cancellation_strikes');
        $this->refresh();
        $this->blockIfNeeded();
    }

    /**
     * Blocks the user from booking if their cancellation strikes reach 3 or more.
     *
     * @return void
     */
    public function blockIfNeeded(): void
    {
        if ($this->cancellation_strikes >= 3 && !$this->is_blocked_from_booking) {
            $this->update(['is_blocked_from_booking' => true]);
        }
    }

    /**
     * Returns whether the user's membership is expiring within the given number of days.
     *
     * @param  int  $daysThreshold
     * @return bool
     */
    public function isMembershipExpiringSoon(int $daysThreshold = 7): bool
    {
        if (!$this->membershipPlan) {
            return false;
        }

        return $this->membership_status === 'active'
            && $this->membershipPlan->updated_at?->diffInDays(now(), false) >= (30 - $daysThreshold);
    }

    /**
     * Relationship: the gym this user is currently assigned to.
     *
     * @return BelongsTo
     */
    public function currentGym(): BelongsTo
    {
        return $this->belongsTo(Gym::class, 'current_gym_id');
    }

    /**
     * Relationship: the membership plan this user is subscribed to.
     *
     * @return BelongsTo
     */
    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    /**
     * Relationship: the user's personalised settings record.
     *
     * @return HasOne
     */
    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    /**
     * Relationship: all body metric records belonging to this user.
     *
     * @return HasMany
     */
    public function bodyMetrics(): HasMany
    {
        return $this->hasMany(BodyMetric::class);
    }

    /**
     * Relationship: all bookings made by this user.
     *
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Relationship: all routines created by this user.
     *
     * @return HasMany
     */
    public function createdRoutines(): HasMany
    {
        return $this->hasMany(Routine::class, 'creator_id');
    }

    /**
     * Relationship: all notifications sent by this user.
     *
     * @return HasMany
     */
    public function sentNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'sender_id');
    }

    /**
     * Relationship: all staff attendance records for this user.
     *
     * @return HasMany
     */
    public function staffAttendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class, 'staff_id');
    }

    /**
     * Relationship: all meal schedule entries for this user.
     *
     * @return HasMany
     */
    public function mealSchedules(): HasMany
    {
        return $this->hasMany(UserMealSchedule::class);
    }

    /**
     * Relationship: all favourite items saved by this user.
     *
     * @return HasMany
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    /**
     * Relationship: all routines this user has activated.
     *
     * @return BelongsToMany
     */
    public function activeRoutines(): BelongsToMany
    {
        return $this->belongsToMany(Routine::class, 'user_active_routines')
                    ->withPivot('is_active', 'start_date');
    }

    /**
     * Relationship: all partner users linked to this user.
     *
     * @return BelongsToMany
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_partners',
            'primary_user_id',
            'partner_user_id'
        )->withPivot('linked_at');
    }

    /**
     * Relationship: the most recent body metric record for this user.
     *
     * @return HasOne
     */
    public function latestBodyMetric(): HasOne
    {
        return $this->hasOne(BodyMetric::class)->latestOfMany('date');
    }

    /**
     * Relationship: routines currently active for this user (pivot is_active = true).
     *
     * @return BelongsToMany
     */
    public function currentActiveRoutine(): BelongsToMany
    {
        return $this->belongsToMany(Routine::class, 'user_active_routines')
                    ->withPivot('is_active', 'start_date')
                    ->wherePivot('is_active', true);
    }
}
