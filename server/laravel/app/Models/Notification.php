<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents an application broadcast notification sent to a target audience.
 *
 * SRP: Encapsulates notification metadata and recipient resolution logic.
 * OCP: New audience types are handled by extending resolveRecipients() without altering existing logic.
 *
 * @property int                             $id
 * @property int|null                        $sender_id
 * @property string                          $title
 * @property string|null                     $body
 * @property string|null                     $target_audience
 * @property int|null                        $related_gym_id
 * @property \Illuminate\Support\Carbon      $created_at
 *
 * @property-read \App\Models\User|null $sender
 * @property-read \App\Models\Gym|null  $relatedGym
 */
class Notification extends Model
{
    /** @var bool */
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

    /** @var array<string, string> */
    protected $casts = [
        'sender_id'      => 'integer',
        'related_gym_id' => 'integer',
        'created_at'     => 'datetime',
    ];

    /**
     * Relationship: the user who sent this notification.
     *
     * @return BelongsTo
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relationship: the gym this notification targets, if applicable.
     *
     * @return BelongsTo
     */
    public function relatedGym(): BelongsTo
    {
        return $this->belongsTo(Gym::class, 'related_gym_id');
    }

    /**
     * Resolves and returns the collection of users who should receive this notification
     * based on the target_audience value.
     *
     * @return Collection
     */
    public function resolveRecipients(): Collection
    {
        return match ($this->target_audience) {
            'global'       => User::all(),
            'staff_only'   => User::whereIn('role', ['admin', 'manager', 'staff'])->get(),
            'specific_gym' => User::where('current_gym_id', $this->related_gym_id)->get(),
            default        => collect(),
        };
    }
}