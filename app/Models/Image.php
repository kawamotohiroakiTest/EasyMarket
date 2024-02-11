<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;

/**
 * @property int $id
 * @property string $file_path
 * @property string $mime_type
 * @property int $size
 * @property int $width
 * @property int $height
 * 
 * @method \App\Presenters\ImagePresenter present()
 */
class Image extends Model
{
    use HasFactory;
    use PresentableTrait;

    protected $presenter = \App\Presenters\ImagePresenter::class;

    protected $fillable = [
        'file_path', 'mime_type', 'size', 'width', 'height',
    ];

}