<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a type of physical activity or discipline.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $intensity_level
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GymClass> $classes
 */

class Activity extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'intensity_level',
    ];

    /** @var array<string,string> */
    protected $casts = [];

    public function classes(): HasMany
    {
        return $this->hasMany(GymClass::class, 'activity_id');
    }
}