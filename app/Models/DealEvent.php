<?php

namespace App\Models;

use App\Enums\DealEventActorType;
use App\Enums\DealEventEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $deal_id
 * @property string $actor_type
 * @property string $event_type
 * @property \Illuminate\Support\Carbon $created_at
 * 
 * @property-read \App\Models\Deal $deal
 * @property-read \Illuminate\Database\Eloquent\Model $deal_eventable
 */
class DealEvent extends Model
{
    use HasFactory;

    protected $casts = [
        'actor_type' => DealEventActorType::class,
        'event_type' => DealEventEventType::class,
    ];

    protected $fillable = [
        'actor_type', 'event_type'
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
    
    public function deal_eventable(): MorphTo
    {
        return $this->morphTo();
    }
}
