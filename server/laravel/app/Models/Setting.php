<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Setting model – one-to-one with User.
 *
 * SRP: Holds per-user UI/privacy preferences.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $user_id
 * @property bool        $share_workout_stats
 * @property bool        $share_body_metrics
 * @property bool        $share_attendance
 * @property string|null $theme_preference
 * @property string|null $language_preference
 */
class Setting extends Model
{
    use HasFactory;

    /** @var string user_id is both PK and FK. */
    protected $primaryKey = 'user_id';

    /** @var bool */
    public $incrementing = false;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'share_workout_stats',
        'share_body_metrics',
        'share_attendance',
        'theme_preference',
        'language_preference',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'share_workout_stats' => 'boolean',
        'share_body_metrics'  => 'boolean',
        'share_attendance'    => 'boolean',
        'theme_preference'    => 'boolean',
    ];

    /**
     * @return BelongsTo<User, Setting>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
