<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AppNotification model.
 *
 * Named AppNotification to avoid collisions with Laravel's built-in
 * Illuminate\Notifications\Notification class.
 * Maps to the 'notifications' table.
 *
 * SRP: Represents a broadcast notification sent by the platform.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property int|null    $sender_id
 * @property string|null $title
 * @property string|null $body
 * @property string|null $target_audience  global|staff_only|specific_gym|specific_user
 * @property int|null    $related_gym_id
 * @property string      $created_at
 */
class AppNotification extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'notifications';

    /** @var bool Only created_at, no updated_at. */
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
        'created_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, AppNotification>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * @return BelongsTo<Gym, AppNotification>
     */
    public function relatedGym(): BelongsTo
    {
        return $this->belongsTo(Gym::class, 'related_gym_id');
    }
}
