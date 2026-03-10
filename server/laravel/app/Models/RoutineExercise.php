<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RoutineExercise pivot model with extra columns.
 *
 * SRP: Represents the ordered placement of an exercise within a routine.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int      $routine_id
 * @property int      $exercise_id
 * @property int|null $order_index
 * @property int|null $recommended_sets
 * @property int|null $recommended_reps
 * @property int|null $rest_seconds
 */
class RoutineExercise extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'routine_exercises';

    /** @var bool */
    public $incrementing = false;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'routine_id',
        'exercise_id',
        'order_index',
        'recommended_sets',
        'recommended_reps',
        'rest_seconds',
    ];

    /**
     * @return BelongsTo<Routine, RoutineExercise>
     */
    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    /**
     * @return BelongsTo<Exercise, RoutineExercise>
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
