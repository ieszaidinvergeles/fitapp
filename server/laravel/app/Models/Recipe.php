<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Recipe model.
 *
 * SRP: Represents a nutritional recipe.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $ingredients
 * @property string|null $preparation_steps
 * @property int|null    $calories
 * @property array|null  $macros_json
 * @property string|null $type
 * @property string|null $image_url
 */
class Recipe extends Model
{
    use HasFactory;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'ingredients',
        'preparation_steps',
        'calories',
        'macros_json',
        'type',
        'image_url',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'macros_json' => 'array',
    ];

    /**
     * Returns all meal schedule entries that reference this recipe.
     *
     * @return HasMany<UserMealSchedule>
     */
    public function userMealSchedules(): HasMany
    {
        return $this->hasMany(UserMealSchedule::class);
    }
}
