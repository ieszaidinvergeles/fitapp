<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DietPlan model.
 *
 * SRP: Represents a named diet plan that can be associated with routines.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property string|null $name
 * @property string|null $goal_description
 */
class DietPlan extends Model
{
    use HasFactory;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'goal_description',
    ];

    /**
     * Returns all routines linked to this diet plan.
     *
     * @return HasMany<Routine>
     */
    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class, 'associated_diet_plan_id');
    }
}
