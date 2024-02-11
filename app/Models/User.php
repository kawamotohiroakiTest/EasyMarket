<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laracasts\Presenter\PresentableTrait;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $email
 * @property string $name
 * @property string $postal_code
 * @property string $address
 * @property string $tel
 * @property string $nickname
 * @property string $description
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection<Deal> $dealsAsSeller
 * @property-read \Illuminate\Database\Eloquent\Collection<Deal> $dealsAsBuyer
 * @property-read \Illuminate\Database\Eloquent\Collection<DealEvent> $dealEvents
 * @property-read \App\Models\Image|null $profileImage
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
    use PresentableTrait;

    protected $presenter = \App\Presenters\UserPresenter::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email', 'password', 'name',
        'postal_code', 'address', 'tel',
        'nickname', 'description',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function dealsAsSeller(): HasMany
    {
        return $this->hasMany(Deal::class, 'seller_id');
    }
    
    public function dealsAsBuyer(): HasMany
    {
        return $this->hasMany(Deal::class, 'buyer_id');
    }

    public function dealEvents(): MorphMany
    {
        return $this->morphMany(DealEvent::class, 'deal_eventable');
    }

    public function profileImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'profile_image_id');
    }
}
