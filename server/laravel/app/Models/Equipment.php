<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Equipment model.
 *
 * SRP: Represents a piece of gym equipment.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property string|null $name
 * @property string|null $description
 * @property bool|null   $is_home_accessible
 */
class Equipment extends Model
{
    use HasFactory;

    /** @var bool */
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

    /**
     * Returns all gym-inventory records that reference this equipment.
     *
     * @return HasMany<GymInventory>
     */
    public function gymInventory(): HasMany
    {
        return $this->hasMany(GymInventory::class);
    }
}
