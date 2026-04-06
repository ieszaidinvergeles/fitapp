<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a nutritional recipe that can be scheduled in user meal plans.
 *
 * SRP: Encapsulates recipe content, macro data, and filtering scopes.
 * OCP: New filters are added as scopes without modifying core recipe logic.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $description
 * @property string      $ingredients
 * @property string      $preparation_steps
 * @property int         $calories
 * @property array|null  $macros_json
 * @property string      $type
 * @property string|null $image_url
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserMealSchedule> $mealSchedules
 */
class Recipe extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'ingredients',
        'preparation_steps',
        'calories',
        'macros_json',
        'type',
        'image_url',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'macros_json' => 'array',
        'calories'    => 'integer',
    ];

    /**
     * Relationship: all meal schedule entries that include this recipe.
     *
     * @return HasMany
     */
    public function mealSchedules(): HasMany
    {
        return $this->hasMany(UserMealSchedule::class);
    }

    /**
     * Returns the macronutrient breakdown for this recipe as an array.
     *
     * @return array
     */
    public function getMacros(): array
    {
        return $this->macros_json ?? [];
    }

    /**
     * Scope: filters recipes by meal type.
     *
     * @param  Builder  $query
     * @param  string   $type
     * @return Builder
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: filters recipes within a calorie range.
     *
     * @param  Builder  $query
     * @param  int      $min
     * @param  int      $max
     * @return Builder
     */
    public function scopeByCalorieRange(Builder $query, int $min, int $max): Builder
    {
        return $query->whereBetween('calories', [$min, $max]);
    }
}