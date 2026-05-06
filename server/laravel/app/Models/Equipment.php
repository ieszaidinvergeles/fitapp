<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a piece of gym equipment that can be inventoried across gyms.
 *
 * SRP: Encapsulates equipment metadata, home-accessibility filtering, and gym inventory pivot.
 * OCP: New inventory attributes are added via pivot without changing the model.
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $description
 * @property bool        $is_home_accessible
 * @property string|null $image_url
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gym> $gyms
 */
class Equipment extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'is_home_accessible',
        'image_url',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_home_accessible' => 'boolean',
    ];

    /**
     * Relationship: gyms that stock this equipment, with inventory pivot data.
     *
     * @return BelongsToMany
     */
    public function gyms(): BelongsToMany
    {
        return $this->belongsToMany(Gym::class, 'gym_inventory')
                    ->withPivot('quantity', 'status');
    }

    /**
     * Scope: filters equipment accessible for home use.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeHomeAccessible(Builder $query): Builder
    {
        return $query->where('is_home_accessible', true);
    }

    /**
     * Returns the inventory status of this equipment in a specific gym.
     * Returns null if the equipment is not stocked in that gym.
     *
     * @param  int  $gymId
     * @return string|null
     */
    public function statusInGym(int $gymId): ?string
    {
        $pivot = $this->gyms()->wherePivot('gym_id', $gymId)->first();

        return $pivot?->pivot->status;
    }
}