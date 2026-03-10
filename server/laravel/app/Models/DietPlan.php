
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a dietary strategy or plan.
 *
 * @property int $id
 * @property string $name
 * @property string|null $goal_description
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Routine> $routines
 */

class DietPlan extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'goal_description',
    ];

    /** @var array<string,string> */
    protected $casts = [];

    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class, 'associated_diet_plan_id');
    }
}