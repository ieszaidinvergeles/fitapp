<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a nutritional diet plan that can be associated with routines.
 *
 * SRP: Encapsulates diet plan metadata and its relationship to routines.
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $goal_description
 * @property string|null $cover_image_url
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Routine> $routines
 */
class DietPlan extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'goal_description',
        'cover_image_url',
    ];

    /**
     * Relationship: all routines associated with this diet plan.
     *
     * @return HasMany
     */
    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class, 'associated_diet_plan_id');
    }
}