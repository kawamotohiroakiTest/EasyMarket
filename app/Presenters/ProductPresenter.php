<?php

namespace App\Presenters;

use Laracasts\Presenter\Presenter;

/** @mixin \App\Models\Product */
class ProductPresenter extends Presenter
{
    public function imageUrl(): ?string
    {
        $urls = $this->imageUrls();

        return $urls[0] ?? null;
    }

    public function imageUrls(): array
    {
        return $this->images->map(function ($image) {
            /** @var \App\Models\Image $image */
            return $image->present()->url();
        })->toArray();
    }
}