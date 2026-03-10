<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GymInventory model.
 *
 * Pivot-style model with extra columns (quantity, status).
 * Composite primary key: [gym_id, equipment_id].
 *
 * SRP: Represents the equipment stock entry for a gym.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $gym_id
 * @property int         $equipment_id
 * @property int|null    $quantity
 * @property string|null $status  operational|maintenance
 */
class GymInventory extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'gym_inventory';

    /** @var bool Composite PK – no auto-incrementing id. */
    public $incrementing = false;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'gym_id',
        'equipment_id',
        'quantity',
        'status',
    ];

    /**
     * @return BelongsTo<Gym, GymInventory>
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * @return BelongsTo<Equipment, GymInventory>
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
