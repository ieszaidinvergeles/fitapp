<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents the personalised configuration settings for a single user.
 *
 * SRP: Encapsulates privacy preferences, UI configuration, and reset logic.
 * OCP: New preference keys are added to fillable and casts without altering existing methods.
 *
 * @property int    $user_id
 * @property bool   $share_workout_stats
 * @property bool   $share_body_metrics
 * @property bool   $share_attendance
 * @property bool   $theme_preference
 * @property string $language_preference
 *
 * @property-read \App\Models\User $user
 */
class Setting extends Model
{
    /** @var string */
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

    /** @var array<string, string> */
    protected $casts = [
        'user_id'             => 'integer',
        'share_workout_stats' => 'boolean',
        'share_body_metrics'  => 'boolean',
        'share_attendance'    => 'boolean',
        'theme_preference'    => 'boolean',
    ];

    /**
     * Relationship: the user these settings belong to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Resets all privacy and UI preferences to their default values.
     *
     * @return void
     */
    public function resetToDefaults(): void
    {
        $this->update([
            'share_workout_stats' => true,
            'share_body_metrics'  => false,
            'share_attendance'    => true,
            'theme_preference'    => false,
            'language_preference' => 'es',
        ]);
    }

    /**
     * Returns whether the given sharing preference is enabled.
     *
     * @param  string  $key
     * @return bool
     */
    public function shares(string $key): bool
    {
        return (bool) ($this->{$key} ?? false);
    }
}