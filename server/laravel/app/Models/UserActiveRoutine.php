<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserActiveRoutine model.
 *
 * SRP: Tracks which routine a user is currently following.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int      $user_id
 * @property int      $routine_id
 * @property bool     $is_active
 * @property string   $start_date
 */
class UserActiveRoutine extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'user_active_routines';

    /** @var bool */
    public $incrementing = false;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'routine_id',
        'is_active',
        'start_date',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'is_active'  => 'boolean',
        'start_date' => 'date',
    ];

    /**
     * @return BelongsTo<User, UserActiveRoutine>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Routine, UserActiveRoutine>
     */
    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }
}
