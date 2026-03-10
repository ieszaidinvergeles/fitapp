
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a user's reservation for a gym class.
 *
 * @property int $id
 * @property int $class_id
 * @property int $user_id
 * @property string $status
 * @property \Illuminate\Support\Carbon $booked_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 *
 * @property-read \App\Models\GymClass $gymClass
 * @property-read \App\Models\User $user
 */

class Booking extends Model
{
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
        'class_id' => 'integer',
        'user_id' => 'integer',
        'booked_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClass::class, 'class_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}