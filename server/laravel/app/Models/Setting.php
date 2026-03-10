
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents the configuration settings for a specific user.
 *
 * @property int $user_id
 * @property bool $share_workout_stats
 * @property bool $share_body_metrics
 * @property bool $share_attendance
 * @property bool $theme_preference
 * @property string $language_preference
 *
 * @property-read \App\Models\User $user
 */

class Setting extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;
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
        'user_id' => 'integer',
        'share_workout_stats' => 'boolean',
        'share_body_metrics' => 'boolean',
        'share_attendance' => 'boolean',
        'theme_preference' => 'boolean', 
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}