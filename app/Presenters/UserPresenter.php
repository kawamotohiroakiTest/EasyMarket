<?php

namespace App\Presenters;

use Laracasts\Presenter\Presenter;

/** @mixin \App\Models\User */
class UserPresenter extends Presenter
{
    public function profileImageUrl(): ?string
    {
        return $this->profileImage?->present()->url();
    }
}