
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Represents a user's saved favorite entity (polymorphic).
 *
 * @property int $user_id
 * @property string $entity_type
 * @property int $entity_id
 *
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Model $entity
 */

class UserFavorite extends Model
{
    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'user_id' => 'integer',
        'entity_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}