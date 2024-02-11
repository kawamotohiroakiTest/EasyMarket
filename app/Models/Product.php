<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laracasts\Presenter\PresentableTrait;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $price
 * 
 * @property-read \App\Models\Deal $deal
 * @property-read \Illuminate\Database\Eloquent\Collection<Image> $images
 */
class Product extends Model
{
    use HasFactory;
    use PresentableTrait;

    protected $presenter = \App\Presenters\ProductPresenter::class;

    protected $fillable = [
        'name', 'description', 'price',
    ];

    public function deal(): HasOne
    {
        return $this->hasOne(Deal::class);
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class);
    }
}