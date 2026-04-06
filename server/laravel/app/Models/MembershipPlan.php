<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a membership plan available in the system.
 *
 * SRP: Encapsulates plan configuration, partner-link eligibility, and user relationships.
 * OCP: Business rules can be extended without modifying base plan structure.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $type
 * @property bool        $allow_partner_link
 * @property string      $price
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $activeUsers
 */
class MembershipPlan extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'type',
        'allow_partner_link',
        'price',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'allow_partner_link' => 'boolean',
        'price'              => 'decimal:2',
    ];

    /**
     * Returns whether this plan allows linking a partner account.
     *
     * @return bool
     */
    public function allowsPartnerLink(): bool
    {
        return $this->allow_partner_link;
    }

    /**
     * Relationship: users on this plan whose membership is currently active.
     *
     * @return HasMany
     */
    public function activeUsers(): HasMany
    {
        return $this->hasMany(User::class)
                    ->where('membership_status', 'active');
    }

    /**
     * Relationship: all users subscribed to this plan.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}