<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserFavorite model.
 *
 * Stores a generic favourite reference (gym, activity, routine)
 * using entity_type + entity_id columns (manual polymorphism).
 *
 * SRP: Represents a single saved favourite for a user.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int    $user_id
 * @property string $entity_type  gym|activity|routine
 * @property int    $entity_id
 */
class UserFavorite extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'user_favorites';

    /** @var bool */
    public $incrementing = false;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
    ];

    /**
     * @return BelongsTo<User, UserFavorite>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
