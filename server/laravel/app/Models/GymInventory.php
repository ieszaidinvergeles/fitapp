<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymInventory extends Model
{
    protected $table = 'gym_inventory';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'gym_id',
        'equipment_id',
        'quantity',
        'status',
    ];

    protected $casts = [
        'gym_id'       => 'integer',
        'equipment_id' => 'integer',
        'quantity'     => 'integer',
    ];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}
