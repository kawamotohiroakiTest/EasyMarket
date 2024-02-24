<?php

namespace App\Http\Controllers\easymarket\API;

use App\Exceptions\APIBusinessLogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\easymarket\API\ProductDeal\CancelRequest;
use App\Http\Requests\easymarket\API\ProductDeal\CreatePaymentIntentRequest;
use App\Http\Requests\easymarket\API\ProductDeal\VerifyPaymentIntentRequest;
use App\Http\Requests\easymarket\API\ProductDeal\ReportDeliveryRequest;
use App\Http\Requests\easymarket\API\ProductDeal\ReportReceiptRequest;
//
use App\Http\Resources\easymarket\API\PaymentIntentResource;
use App\Http\Resources\easymarket\API\DealForMyPageResource;
use App\Models\Product;

use App\Services\easymarket\DealService\DealServiceInterface;
use App\Services\easymarket\DealService\Exceptions\InvalidStatusTransitionException;
use App\Services\easymarket\DealService\Exceptions\IncompleteBuyerShippingInfoException;
use App\Services\easymarket\DealService\Exceptions\PaymentIntentIsNotSucceededException;
use App\Policies\EasyMarket\API\DealPolicy;


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

    /**
     * 商品支払いインテント確認API
     * 
     * @param  VerifyPaymentIntentRequest  $request
     * @param  Product  $product
     * @return DealForMypageResource
     */
    public function verifyPaymentIntent(VerifyPaymentIntentRequest $request, Product $product)
    {
        $params = $request->safe();
        /** @var \App\Models\User $buyer */
        $buyer = Auth::user();

        try {
            $deal = $this->dealService->verifyPaymentIntent($product->deal, $buyer, $params['payment_intent_id']);
        } catch (InvalidStatusTransitionException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        } catch (IncompleteBuyerShippingInfoException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        } catch (PaymentIntentIsNotSucceededException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        }
        $deal->load('dealEvents');

        return new DealForMyPageResource($deal);
    }

    /**
     * 商品出品キャンセルAPI
     * 
     * @param  CancelRequest  $request
     * @param  Product  $product
     * @return DealForMyPageResource
     */
    public function cancel(CancelRequest $request, Product $product)
    {
        /** @var \App\Models\User $seller */
        $seller = Auth::user();

        try {
            $deal = $this->dealService->cancel($product->deal, $seller);
        } catch (InvalidStatusTransitionException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        }
        $deal->load('dealEvents');

        return new DealForMyPageResource($deal);
    }

    /**
     * 配送報告API
     * 
     * @param  ReportDeliveryRequest  $request
     * @param  Product  $product
     * @return DealForMyPageResource
     */
    public function reportDelivery(ReportDeliveryRequest $request, Product $product)
    {
        /** @var \App\Models\User $seller */
        $seller = Auth::user();

        try {
            $deal = $this->dealService->reportDelivery($product->deal, $seller);
        } catch (InvalidStatusTransitionException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        }
        $deal->load('dealEvents');

        return new DealForMyPageResource($deal);
    }

    /**
     * 受取報告API
     * 
     * @param  ReportReceiptRequest  $request
     * @param  Product  $product
     * @return DealForMyPageResource
     */
    public function reportReceipt(ReportReceiptRequest $request, Product $product)
    {
        /** @var \App\Models\User $buyer */
        $buyer = Auth::user();

        try {
            $deal = $this->dealService->reportReceipt($product->deal, $buyer);
        } catch (InvalidStatusTransitionException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        }
        $deal->load('dealEvents');

        return new DealForMyPageResource($deal);
    }


    


}
