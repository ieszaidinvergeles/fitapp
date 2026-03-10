<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BodyMetric model.
 *
 * SRP: Represents a single body-metric snapshot for a user.
 * DIP: Depends on the Eloquent Model abstraction.
 *
 * @property int         $id
 * @property int         $user_id
 * @property string|null $date
 * @property float|null  $weight_kg
 * @property float|null  $height_cm
 * @property float|null  $body_fat_pct
 * @property float|null  $muscle_mass_pct
 */
class BodyMetric extends Model
{
    use HasFactory;

    /** @var bool No generic timestamps on this table. */
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
        'date'            => 'date',
        'weight_kg'       => 'decimal:2',
        'height_cm'       => 'decimal:2',
        'body_fat_pct'    => 'decimal:2',
        'muscle_mass_pct' => 'decimal:2',
    ];

    // date -> fecha del dia sin la hora || datetime -> fecha del dia con la hora 

    /**
     * Returns the user these metrics belong to.
     *
     * @return BelongsTo<User, BodyMetric>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
