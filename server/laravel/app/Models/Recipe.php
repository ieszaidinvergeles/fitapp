
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a nutritional recipe.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $ingredients
 * @property string $preparation_steps
 * @property int $calories
 * @property array|null $macros_json
 * @property string $type
 * @property string|null $image_url
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserMealSchedule> $mealSchedules
 */

class Recipe extends Model
{
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
        'calories' => 'integer',
        'macros_json' => 'array', 
    ];

    public function mealSchedules(): HasMany
    {
        return $this->hasMany(UserMealSchedule::class);
    }
}