
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a scheduled meal for a user on a specific date.
 *
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $date
 * @property string $meal_type
 * @property int $recipe_id
 * @property bool $is_consumed
 *
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Recipe $recipe
 */

class UserMealSchedule extends Model
{
    protected $table = 'user_meal_schedule';
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'date',
        'meal_type',
        'recipe_id',
        'is_consumed',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'user_id' => 'integer',
        'recipe_id' => 'integer',
        'date' => 'date',
        'is_consumed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}