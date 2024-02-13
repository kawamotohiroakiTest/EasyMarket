<?php

namespace App\Services\easymarket\ImageService;

use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use App\Models\Image;

class ImageService implements ImageServiceInterface
{
    /*
     * 複数の画像を保存
     * 
     * @param UploadFiles[] $files
     * @return Collection<Image>
     */
    public function saveUploadFiles(array $files): Collection
    {
        $images = new Collection();

        foreach ($files as $file) {
            $images->push($this->saveUploadFile($file));
        }

        return $images;
    }

    /*
     * 画像を保存
     * 
     * @param UploadedFile $file
     * @return Image
     */
    public function saveUploadFile(UploadedFile $file): Image
    {
        // ファイルをストレージに保存し、そのパスを取得
        $path = $file->store('images', 'public');

        $imagePath = $file->getRealPath();
        list($width, $height) = getimagesize($imagePath);

        $image = new Image([
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
        ]);

        $image->save();

        return $image;
    }
}
