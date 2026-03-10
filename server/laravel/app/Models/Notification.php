
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents an application broadcast notification.
 *
 * @property int $id
 * @property int $sender_id
 * @property string $title
 * @property string|null $body
 * @property string|null $target_audience
 * @property int $related_gym_id
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @property-read \App\Models\User $sender
 * @property-read \App\Models\Gym $relatedGym
 */

class Notification extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'sender_id',
        'title',
        'body',
        'target_audience',
        'related_gym_id',
        'created_at',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'sender_id' => 'integer',
        'related_gym_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function relatedGym(): BelongsTo
    {
        return $this->belongsTo(Gym::class, 'related_gym_id');
    }
}