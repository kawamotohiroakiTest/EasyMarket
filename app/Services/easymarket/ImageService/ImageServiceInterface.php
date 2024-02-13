<?php

namespace App\Services\easymarket\ImageService;

use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use App\Models\Image;

interface ImageServiceInterface
{
    /*
     * @return Collection<Image>
     */
    public function saveUploadFiles(array $files): Collection;
    public function saveUploadFile(UploadedFile $file): Image;
}