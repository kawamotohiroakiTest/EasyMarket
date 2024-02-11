<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $email
 * @property string $name
 * @method tokens()
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection<DealEvent> $dealEvents
 */
class AdminUser extends Authenticatable
{
    use HasApiTokens, HasFactory;

    public function dealEvents(): MorphMany
    {
        return $this->morphMany(DealEvent::class, 'deal_eventable');
    }
}
