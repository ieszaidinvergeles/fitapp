<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Routine model.
 *
 * SRP: Represents a workout routine definition.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property string|null $name
 * @property string|null $description
 * @property int|null    $creator_id
 * @property string|null $difficulty_level
 * @property int|null    $estimated_duration_min
 * @property int|null    $associated_diet_plan_id
 */
class Routine extends Model
{
    use HasFactory;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'creator_id',
        'difficulty_level',
        'estimated_duration_min',
        'associated_diet_plan_id',
    ];

    /**
     * @return BelongsTo<User, Routine>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * @return BelongsTo<DietPlan, Routine>
     */
    public function dietPlan(): BelongsTo
    {
        return $this->belongsTo(DietPlan::class, 'associated_diet_plan_id');
    }

    /**
     * Returns all exercise pivot records in this routine.
     *
     * @return HasMany<RoutineExercise>
     */
    public function routineExercises(): HasMany
    {
        return $this->hasMany(RoutineExercise::class);
    }

    /**
     * Returns all user-active-routine records for this routine.
     *
     * @return HasMany<UserActiveRoutine>
     */
    public function userActiveRoutines(): HasMany
    {
        return $this->hasMany(UserActiveRoutine::class);
    }
}
