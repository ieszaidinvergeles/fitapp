<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $recipes
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

    /**
     * Relationship: all recipes in this diet plan.
     *
     * @return BelongsToMany
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'diet_plan_recipes')
                    ->withPivot('meal_type');
    }
}