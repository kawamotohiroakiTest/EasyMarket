<?php

namespace App\Presenters;

use Illuminate\Support\Facades\Storage;
use Laracasts\Presenter\Presenter;

/** @mixin \App\Models\Image */
class ImagePresenter extends Presenter
{
    public function url(): string
    {
        return asset(Storage::url($this->file_path));
    }
}