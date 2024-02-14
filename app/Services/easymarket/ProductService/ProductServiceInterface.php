<?php

namespace App\Services\easymarket\ProductService;

use App\Models\Product;
use App\Services\easymarket\ProductService\Dtos\StoreCommand;
use Illuminate\Support\Collection;

interface ProductServiceInterface
{
    /*
     * @return Collection<Product>
     */
    public function get(): Collection;
    public function store(StoreCommand $storeCommand): Product;
}