<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a snapshot of a user's physical metrics.
 *
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $date
 * @property string $weight_kg
 * @property string $height_cm
 * @property string|null $body_fat_pct
 * @property string|null $muscle_mass_pct
 *
 * @property-read \App\Models\User $user
 */

class BodyMetric extends Model
{
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

    /** @var array<string,string> */
    protected $casts = [
        'user_id' => 'integer',
        'date' => 'date',
        'weight_kg' => 'decimal:1',
        'height_cm' => 'decimal:1',
        'body_fat_pct' => 'decimal:2',
        'muscle_mass_pct' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}