<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a snapshot of a user's physical body metrics on a specific date.
 *
 * SRP: Encapsulates body measurement data, BMI computation, and metric delta calculation.
 * OCP: New derived metrics are added as methods without altering the data structure.
 *
 * @property int         $id
 * @property int         $user_id
 * @property \Illuminate\Support\Carbon $date
 * @property string      $weight_kg
 * @property string      $height_cm
 * @property string|null $body_fat_pct
 * @property string|null $muscle_mass_pct
 *
 * @property-read \App\Models\User $user
 */
class BodyMetric extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'date',
        'weight_kg',
        'height_cm',
        'body_fat_pct',
        'muscle_mass_pct',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'user_id'         => 'integer',
        'date'            => 'date',
        'weight_kg'       => 'decimal:1',
        'height_cm'       => 'decimal:1',
        'body_fat_pct'    => 'decimal:2',
        'muscle_mass_pct' => 'decimal:2',
    ];

    /**
     * Relationship: the user this body metric record belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculates the Body Mass Index (BMI) from weight and height.
     *
     * @return float
     */
    public function bmi(): float
    {
        $heightM = (float) $this->height_cm / 100;

        return round((float) $this->weight_kg / ($heightM ** 2), 2);
    }

    /**
     * Scope: filters body metrics for a specific user.
     *
     * @param  Builder  $query
     * @param  int      $userId
     * @return Builder
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Returns an array of deltas between this metric and a previous one.
     *
     * @param  self  $previous
     * @return array
     */
    public function variationFrom(self $previous): array
    {
        return [
            'weight_kg'       => round((float) $this->weight_kg - (float) $previous->weight_kg, 1),
            'height_cm'       => round((float) $this->height_cm - (float) $previous->height_cm, 1),
            'body_fat_pct'    => round((float) $this->body_fat_pct - (float) $previous->body_fat_pct, 2),
            'muscle_mass_pct' => round((float) $this->muscle_mass_pct - (float) $previous->muscle_mass_pct, 2),
            'bmi'             => round($this->bmi() - $previous->bmi(), 2),
        ];
    }
}