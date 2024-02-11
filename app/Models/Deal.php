<?php

namespace App\Models;

use App\Enums\DealStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $product_id
 * @property int $seller_id
 * @property int $buyer_id
 * @property \App\Enums\DealStatus $status
 * 
 * @property-read \App\Models\User $buyer
 * @property-read \App\Models\User $seller
 * @property-read \App\Models\Product $product
 * @property-read \Illuminate\Database\Eloquent\Collection<DealEvent> $dealEvents
 * 
 * @property-read bool $is_purchasable
 */
class Deal extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => DealStatus::class
    ];

    protected $attributes = [
        'status' => DealStatus::Listing,
    ];

    protected $fillable = [
        'status',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
    
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    public function dealEvents(): HasMany
    {
        return $this->hasMany(DealEvent::class);
    }

    public function getIsPurchasableAttribute(): bool
    {
        return in_array($this->status, [DealStatus::Listing]);
    }
}
