<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Represents a meal entry in a user's personal meal schedule.
 *
 * SRP: Encapsulates meal scheduling data, date/week scoping, and calorie aggregation.
 * OCP: New schedule filters are added as scopes without modifying core meal logic.
 *
 * @property int                        $id
 * @property int                        $user_id
 * @property \Illuminate\Support\Carbon $date
 * @property string                     $meal_type
 * @property int|null                   $recipe_id
 * @property bool                       $is_consumed
 *
 * @property-read \App\Models\User        $user
 * @property-read \App\Models\Recipe|null $recipe
 */
class UserMealSchedule extends Model
{
    /** @var string */
    protected $table = 'user_meal_schedule';

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'date',
        'meal_type',
        'recipe_id',
        'is_consumed',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'user_id'     => 'integer',
        'recipe_id'   => 'integer',
        'date'        => 'date',
        'is_consumed' => 'boolean',
    ];

    /**
     * Relationship: the user this meal entry belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: the recipe assigned to this meal entry.
     *
     * @return BelongsTo
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Scope: filters meal entries for a specific date.
     *
     * @param  Builder  $query
     * @param  string   $date
     * @return Builder
     */
    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->where('date', $date);
    }

    /**
     * Scope: filters meal entries within the current ISO calendar week.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeForWeek(Builder $query): Builder
    {
        return $query->whereBetween('date', [
            Carbon::now()->startOfWeek()->toDateString(),
            Carbon::now()->endOfWeek()->toDateString(),
        ]);
    }

    /**
     * Returns the total calories consumed by a user on a specific date.
     * Only counts entries marked as consumed with an associated recipe.
     *
     * @param  int     $userId
     * @param  string  $date
     * @return int
     */
    public static function totalCaloriesForDate(int $userId, string $date): int
    {
        return (int) self::where('user_id', $userId)
            ->where('date', $date)
            ->where('is_consumed', true)
            ->join('recipes', 'recipes.id', '=', 'user_meal_schedule.recipe_id')
            ->sum('recipes.calories');
    }
}