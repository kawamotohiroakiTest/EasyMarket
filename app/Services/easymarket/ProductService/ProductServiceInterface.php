<?php

namespace App\Services\easymarket\ProductService;

use App\Models\Product;
use App\Services\easymarket\ProductService\Dtos\StoreCommand;

interface ProductServiceInterface
{
    /*
     * @return Collection<Product>
     */
    public function store(StoreCommand $storeCommand): Product;
}