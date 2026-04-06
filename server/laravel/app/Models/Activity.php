<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a physical activity type that can be scheduled as a gym class.
 *
 * SRP: Encapsulates activity metadata and its relationship to gym classes.
 * OCP: Intensity filtering is exposed via a scoped relationship, not hardcoded logic.
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $description
 * @property string      $intensity_level
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GymClass> $gymClasses
 */
class Activity extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'intensity_level',
    ];

    /**
     * Relationship: all gym classes using this activity type.
     *
     * @return HasMany
     */
    public function gymClasses(): HasMany
    {
        return $this->hasMany(GymClass::class);
    }

    /**
     * Returns all gym classes for this activity filtered by intensity level.
     *
     * @param  string  $level
     * @return HasMany
     */
    public function classesByIntensity(string $level): HasMany
    {
        return $this->gymClasses()->where('intensity_level', $level);
    }
}