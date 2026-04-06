<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a physical exercise that can be included in training routines.
 *
 * SRP: Encapsulates exercise metadata, media availability, and routine pivot relationships.
 * OCP: Muscle group filtering is an open scope, not hardcoded branching logic.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $description
 * @property string|null $image_url
 * @property string|null $video_url
 * @property string|null $target_muscle_group
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Routine> $routines
 */
class Exercise extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'image_url',
        'video_url',
        'target_muscle_group',
    ];

    /**
     * Relationship: routines that include this exercise, with training pivot data.
     *
     * @return BelongsToMany
     */
    public function routines(): BelongsToMany
    {
        return $this->belongsToMany(Routine::class, 'routine_exercises')
                    ->withPivot('order_index', 'recommended_sets', 'recommended_reps', 'rest_seconds');
    }

    /**
     * Scope: filters exercises targeting a specific muscle group.
     *
     * @param  Builder  $query
     * @param  string   $group
     * @return Builder
     */
    public function scopeByMuscleGroup(Builder $query, string $group): Builder
    {
        return $query->where('target_muscle_group', $group);
    }

    /**
     * Returns whether this exercise has both image and video media attached.
     *
     * @return bool
     */
    public function hasMedia(): bool
    {
        return $this->image_url !== null && $this->video_url !== null;
    }
}