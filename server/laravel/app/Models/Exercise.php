
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a specific physical exercise.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string|null $image_url
 * @property string|null $video_url
 * @property string|null $target_muscle_group
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Routine> $routines
 */

class Exercise extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'image_url',
        'video_url',
        'target_muscle_group',
    ];

    /** @var array<string,string> */
    protected $casts = [];

    public function routines(): BelongsToMany
    {
        return $this->belongsToMany(Routine::class, 'routine_exercises')
                    ->withPivot('order_index', 'recommended_sets', 'recommended_reps', 'rest_seconds');
    }
}