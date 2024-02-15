<?php

namespace App\Http\Controllers\easymarket\API;

use App\Exceptions\APIBusinessLogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\easymarket\API\ProductDeal\CancelRequest;
use App\Http\Requests\easymarket\API\ProductDeal\CreatePaymentIntentRequest;
use App\Http\Resources\easymarket\API\PaymentIntentResource;
use App\Models\Product;

use App\Services\easymarket\DealService\DealServiceInterface;
use App\Services\easymarket\DealService\Exceptions\InvalidStatusTransitionException;
use App\Services\easymarket\DealService\Exceptions\IncompleteBuyerShippingInfoException;
use App\Services\easymarket\DealService\Exceptions\PaymentIntentIsNotSucceededException;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProductDealController extends Controller
{

    /**
    * @var DealServiceInterface
    */
    private $dealService;

    /**
     * @param  DealServiceInterface  $dealService
     * @return void
     */
    public function __construct(
        DealServiceInterface $dealService,
    )
    {
        $this->dealService = $dealService;
    }

    /**
     * 商品支払いインテント作成API
     * 
     * @param  CreatePaymentIntentRequest  $request
     * @param  Product  $product
     * @return PaymentIntentResource
     */
    public function createPaymentIntent(CreatePaymentIntentRequest $request, Product $product)
    {
        /** @var \App\Models\User $buyer */
        $buyer = Auth::user();
        try {
            $paymentIntent = $this->dealService->createPaymentIntent($product->deal, $buyer);
        } catch (InvalidStatusTransitionException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        } catch (IncompleteBuyerShippingInfoException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        } catch (PaymentIntentIsNotSucceededException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        }

        return new PaymentIntentResource($paymentIntent);
    }


}
