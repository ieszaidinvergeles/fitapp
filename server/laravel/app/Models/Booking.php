<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Booking model.
 *
 * SRP: Represents a single class booking made by a user.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property int         $class_id
 * @property int         $user_id
 * @property string|null $status   active|cancelled|attended|no_show
 * @property string      $booked_at
 * @property string|null $cancelled_at
 */
class Booking extends Model
{
    use HasFactory;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'class_id',
        'user_id',
        'status',
        'booked_at',
        'cancelled_at',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'booked_at'    => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Returns the gym class this booking is for.
     *
     * @return BelongsTo<GymClass, Booking>
     */
    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClass::class, 'class_id');
    }

    /**
     * Returns the user who made this booking.
     *
     * @return BelongsTo<User, Booking>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //  User::class --> App\Models\User
}
