<?php

namespace App\Services\easymarket\DealService;

use App\Enums\DealStatus;
use App\Models\Deal;
use App\Models\User;
use App\Services\easymarket\DealService\Exceptions\IncompleteBuyerShippingInfoException;
use App\Services\easymarket\DealService\Exceptions\InvalidStatusTransitionException;
use App\Services\easymarket\StripeService\StripeServiceInterface;

use Stripe\PaymentIntent;
use Stripe\Stripe;

class DealService implements DealServiceInterface
{

    /**
    * @var StripeServiceInterface
    */
    private $stripeService;

    /**
     * @param  StripeServiceInterface  $stripeService
     * @return void
     */
    public function __construct(
        StripeServiceInterface $stripeService
    )
    {
        $this->stripeService = $stripeService;
    }

    /*
     * PaymentIntentを作成する
     * 
     * @param Deal $deal
     * @param User $buyer
     * @exception IncompleteBuyerShippingInfoException
     * @exception InvalidStatusTransitionException
     * @return PaymentIntent
     */
    public function createPaymentIntent(Deal $deal, User $buyer): PaymentIntent
    {
        $this->validateToPurchase($deal, $buyer);

        $paymentIntent = $this->stripeService->createPaymentIntent($deal->product, $buyer);

        return $paymentIntent;
    }



    /*
     * 商品購入についてバリデーションチェック
     * 
     * @param Deal $deal
     * @param User $buyer
     * @exception IncompleteBuyerShippingInfoException
     * @exception InvalidStatusTransitionException
     * @return void
     */
    private function validateToPurchase(Deal $deal, User $buyer): void
    {
        if (empty($buyer->name) || empty($buyer->postal_code) || empty($buyer->address)) {
            throw new IncompleteBuyerShippingInfoException();
        }

        if (!in_array($deal->status, [DealStatus::Listing])) {
            throw new InvalidStatusTransitionException();
        }
    }
}
