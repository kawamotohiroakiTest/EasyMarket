<?php

namespace App\Services\easymarket\ProductService\Dtos;

use App\Models\User;

class StoreCommand
{
    public User $seller;
    public string $name;
    public string $description;
    public int $price;
    /** @var \Illuminate\Http\UploadedFile[] */
    public array $images;

    /**
     * @param User $seller
     * @param string $name
     * @param string $description
     * @param int $price
     * @param \Illuminate\Http\UploadedFile[] $images
     */
    public function __construct(
        User $seller,
        string $name,
        string $description,
        int $price,
        array $images
    )
    {
        $this->seller = $seller;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->images = $images;
    }
}