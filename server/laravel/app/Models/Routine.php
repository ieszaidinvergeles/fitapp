
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a structured workout routine.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $creator_id
 * @property string $difficulty_level
 * @property int $estimated_duration_min
 * @property int|null $associated_diet_plan_id
 *
 * @property-read \App\Models\User $creator
 * @property-read \App\Models\DietPlan|null $dietPlan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Exercise> $exercises
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $activeUsers
 */

class Routine extends Model
{
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

    /** @var array<string,string> */
    protected $casts = [
        'creator_id' => 'integer',
        'estimated_duration_min' => 'integer',
        'associated_diet_plan_id' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function dietPlan(): BelongsTo
    {
        return $this->belongsTo(DietPlan::class, 'associated_diet_plan_id');
    }

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'routine_exercises')
                    ->withPivot('order_index', 'recommended_sets', 'recommended_reps', 'rest_seconds');
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_active_routines')
                    ->withPivot('is_active', 'start_date');
    }
}