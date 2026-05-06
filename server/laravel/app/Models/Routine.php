<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a structured workout routine composed of ordered exercises.
 *
 * SRP: Encapsulates routine configuration, exercise ordering, and duplication logic.
 * OCP: New routine behaviors are added as methods without altering core relationships.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $description
 * @property int|null    $creator_id
 * @property string      $difficulty_level
 * @property int         $estimated_duration_min
 * @property int|null    $associated_diet_plan_id
 * @property string|null $cover_image_url
 * @property bool        $is_favorite_flag
 *
 * @property-read \App\Models\User|null                                                   $creator
 * @property-read \App\Models\DietPlan|null                                               $dietPlan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Exercise>     $exercises
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>         $activeUsers
 */
class Routine extends Model
{
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
        'cover_image_url',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'creator_id'              => 'integer',
        'estimated_duration_min'  => 'integer',
        'associated_diet_plan_id' => 'integer',
    ];

    /**
     * Relationship: user that created this routine.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Relationship: the diet plan associated with this routine, if any.
     *
     * @return BelongsTo
     */
    public function dietPlan(): BelongsTo
    {
        return $this->belongsTo(DietPlan::class, 'associated_diet_plan_id');
    }

    /**
     * Relationship: all exercises in this routine with training pivot data.
     *
     * @return BelongsToMany
     */
    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'routine_exercises')
                    ->withPivot('order_index', 'recommended_sets', 'recommended_reps', 'rest_seconds');
    }

    /**
     * Relationship: all users who have activated this routine.
     *
     * @return BelongsToMany
     */
    public function activeUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_active_routines')
                    ->withPivot('is_active', 'start_date');
    }

    /**
     * Returns exercises ordered by their pivot order_index.
     *
     * @return BelongsToMany
     */
    public function orderedExercises(): BelongsToMany
    {
        return $this->exercises()->orderByPivot('order_index');
    }

    /**
     * Returns the count of users who currently have this routine active.
     *
     * @return int
     */
    public function activeUserCount(): int
    {
        return $this->activeUsers()->wherePivot('is_active', true)->count();
    }

    /**
     * Returns whether this routine has an associated diet plan.
     *
     * @return bool
     */
    public function hasDietPlan(): bool
    {
        return $this->associated_diet_plan_id !== null;
    }

    /**
     * Clones this routine and all its exercise pivot entries for a given creator.
     *
     * @param  int  $creatorId
     * @return self
     */
    public function duplicate(int $creatorId): self
    {
        $clone = $this->replicate(['id']);
        $clone->creator_id = $creatorId;
        $clone->save();

        foreach ($this->exercises()->withPivot('order_index', 'recommended_sets', 'recommended_reps', 'rest_seconds')->get() as $exercise) {
            $clone->exercises()->attach($exercise->id, [
                'order_index'       => $exercise->pivot->order_index,
                'recommended_sets'  => $exercise->pivot->recommended_sets,
                'recommended_reps'  => $exercise->pivot->recommended_reps,
                'rest_seconds'      => $exercise->pivot->rest_seconds,
            ]);
        }

        return $clone;
    }
}