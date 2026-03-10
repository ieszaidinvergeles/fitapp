
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a piece of equipment or machine.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $is_home_accessible
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gym> $gyms
 */

class Equipment extends Model
{
    protected $table = 'equipment'; 
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'is_home_accessible',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'is_home_accessible' => 'boolean',
    ];

    public function gyms(): BelongsToMany
    {
        return $this->belongsToMany(Gym::class, 'gym_inventory')
                    ->withPivot('quantity', 'status');
    }
}