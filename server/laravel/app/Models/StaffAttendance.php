
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a clock-in/clock-out record for a staff member.
 *
 * @property int $id
 * @property int $staff_id
 * @property int $gym_id
 * @property \Illuminate\Support\Carbon $clock_in
 * @property \Illuminate\Support\Carbon|null $clock_out
 * @property \Illuminate\Support\Carbon $date
 *
 * @property-read \App\Models\User $staff
 * @property-read \App\Models\Gym $gym
 */

class StaffAttendance extends Model
{
    protected $table = 'staff_attendance';
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'staff_id',
        'gym_id',
        'clock_in',
        'clock_out',
        'date',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'staff_id' => 'integer',
        'gym_id' => 'integer',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'date' => 'date',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}