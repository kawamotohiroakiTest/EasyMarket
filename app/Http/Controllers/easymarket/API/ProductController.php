<?php

namespace App\Http\Controllers\easymarket\API;

use Illuminate\Support\Facades\Auth;
use App\Exceptions\APIBusinessLogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\easymarket\API\Product\StoreRequest;
use App\Http\Resources\easymarket\API\ProductResource;
use App\Services\easymarket\ProductService\Dtos\StoreCommand;
use App\Services\easymarket\ProductService\ProductServiceInterface;
use App\Services\easymarket\ProductService\Exceptions\IncompleteSellerInfoException;

class ProductController extends Controller
{
    /**
    * @var ProductServiceInterface
    */
    private $productService;

    /**
     * @param  ProductServiceInterface  $productService
     * @return void
     */
    public function __construct(
        ProductServiceInterface $productService
    )
    {
        $this->productService = $productService;
    }

    public function store(StoreRequest $request)
    {
        $params = $request->safe();
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $storeCommand = new StoreCommand(
            $user,
            $params['name'],
            $params['description'],
            $params['price'],
            $params['images'],
        );
        try {
            $product = $this->productService->store($storeCommand);
        } catch (IncompleteSellerInfoException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        }

        return new ProductResource($product);
    }

}