<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StaffAttendance model.
 *
 * SRP: Records a staff member's clock-in/out event at a gym.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property int|null    $staff_id
 * @property int|null    $gym_id
 * @property string      $clock_in
 * @property string|null $clock_out
 * @property string|null $date
 */
class StaffAttendance extends Model
{
    use HasFactory;

    /** @var bool */
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
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
        'date'      => 'date',
    ];

    /**
     * @return BelongsTo<User, StaffAttendance>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * @return BelongsTo<Gym, StaffAttendance>
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}
