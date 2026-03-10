<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MembershipPlan model.
 *
 * SRP: Represents a membership plan definition.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int         $id
 * @property string|null $name
 * @property string|null $type  physical|online|duo
 * @property bool        $allow_partner_link
 * @property float|null  $price
 */
class MembershipPlan extends Model
{
    use HasFactory;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'type',
        'allow_partner_link',
        'price',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'allow_partner_link' => 'boolean',
        'price'              => 'decimal:2',
    ];

    /**
     * Returns all users subscribed to this plan.
     *
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
