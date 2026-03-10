<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Exercise model.
 *
 * SRP: Represents a single exercise definition.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $image_url
 * @property string|null $video_url
 * @property string|null $target_muscle_group
 */
class Exercise extends Model
{
    use HasFactory;

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
     * Returns all routine-exercise pivot records for this exercise.
     *
     * @return HasMany<RoutineExercise>
     */
    public function routineExercises(): HasMany
    {
        return $this->hasMany(RoutineExercise::class);
    }
}
