<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserPartner model.
 *
 * SRP: Represents the link between two users sharing a duo membership.
 * DIP: Depends on Eloquent abstraction.
 *
 * @property int    $id
 * @property int    $primary_user_id
 * @property int    $partner_user_id
 * @property string $linked_at
 */
class UserPartner extends Model
{
    use HasFactory;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'primary_user_id',
        'partner_user_id',
        'linked_at',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'linked_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, UserPartner>
     */
    public function primaryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_user_id');
    }

    /**
     * @return BelongsTo<User, UserPartner>
     */
    public function partnerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_user_id');
    }
}
