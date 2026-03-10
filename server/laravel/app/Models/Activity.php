<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Activity model.
 *
 * SRP: Represents an activity entity and exposes its direct DB relationships.
 * DIP: Depends on the Eloquent Model abstraction rather than raw query builders.
 *
 * @property int         $id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $intensity_level
 */
class Activity extends Model
{
    use HasFactory;

    /** @var bool No created_at / updated_at columns on this table. */
    public $timestamps = false;

    /** @var list<string> Mass-assignable attributes. */
    protected $fillable = [
        'name',
        'description',
        'intensity_level',
    ];

    /**
     * Returns every gym class that belongs to this activity.
     *
     * @return HasMany<GymClass>
     */
    public function classes(): HasMany
    {
        return $this->hasMany(GymClass::class, 'activity_id');
    }
}
