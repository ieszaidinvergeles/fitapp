
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a membership plan available in the system.
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property bool $allow_partner_link
 * @property string $price
 * * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 */

class MembershipPlan extends Model
{
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
        'price' => 'decimal:2',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}