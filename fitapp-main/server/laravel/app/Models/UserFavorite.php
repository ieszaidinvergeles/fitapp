<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a saved favourite entity for a user (gym, activity, or routine).
 *
 * SRP: Encapsulates favourite entity references and existence checks.
 * OCP: New entity types are supported via the enum without modifying model logic.
 *
 * @property int    $user_id
 * @property string $entity_type
 * @property int    $entity_id
 *
 * @property-read \App\Models\User $user
 */
class UserFavorite extends Model
{
    /** @var bool */
    public $incrementing = false;

    /** @var mixed */
    protected $primaryKey = null;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'user_id'   => 'integer',
        'entity_id' => 'integer',
    ];

    /**
     * Relationship: the user who saved this favourite.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: filters favourites by a specific entity type.
     *
     * @param  Builder  $query
     * @param  string   $type
     * @return Builder
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('entity_type', $type);
    }

    /**
     * Returns whether a favourite entry already exists for the given combination.
     *
     * @param  int     $userId
     * @param  string  $entityType
     * @param  int     $entityId
     * @return bool
     */
    public static function existsFor(int $userId, string $entityType, int $entityId): bool
    {
        return self::where('user_id', $userId)
                   ->where('entity_type', $entityType)
                   ->where('entity_id', $entityId)
                   ->exists();
    }
}