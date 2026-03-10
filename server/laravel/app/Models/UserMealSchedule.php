<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserMealSchedule model.
 *
 * SRP: Represents one scheduled meal in a user's nutrition plan.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property int         $user_id
 * @property string|null $date
 * @property string|null $meal_type
 * @property int|null    $recipe_id
 * @property bool|null   $is_consumed
 */
class UserMealSchedule extends Model
{
    use HasFactory;

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

    /** @var array<string,string> */
    protected $casts = [
        'date'        => 'date',
        'is_consumed' => 'boolean',
    ];

    /**
     * @return BelongsTo<User, UserMealSchedule>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Recipe, UserMealSchedule>
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
