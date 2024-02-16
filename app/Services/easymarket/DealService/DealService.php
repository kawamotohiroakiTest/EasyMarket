<?php

namespace App\Services\easymarket\DealService;

use App\Enums\DealStatus;
use App\Models\Deal;
use App\Models\User;
use App\Models\DealEvent;
use App\Services\easymarket\DealService\Exceptions\IncompleteBuyerShippingInfoException;
use App\Services\easymarket\DealService\Exceptions\InvalidStatusTransitionException;
use App\Services\easymarket\StripeService\StripeServiceInterface;
use App\Services\easymarket\DealService\Exceptions\PaymentIntentIsNotSucceededException;
use Illuminate\Support\Facades\DB;
use App\Enums\DealEventActorType;
use App\Enums\DealEventEventType;

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

    /*
     * 商品購入
     * 
     * @param Deal $deal
     * @param User $buyer
     * @exception IncompleteBuyerShippingInfoException
     * @exception InvalidStatusTransitionException
     * @exception PaymentIntentIsNotSucceededException
     * @return Deal
     */
    public function verifyPaymentIntent(Deal $deal, User $buyer, string $paymentIntentId): Deal
    {
        $this->validateToPurchase($deal, $buyer);

        if (!$this->stripeService->verifyPaymentIntent($paymentIntentId)) {
            throw new PaymentIntentIsNotSucceededException();
        }

        $deal = DB::transaction(function () use ($deal, $buyer) {
            $deal->buyer()->associate($buyer);
            $deal->status = DealStatus::Purchased;
            $deal->save();

            $dealEvent = new DealEvent([
                'actor_type' => DealEventActorType::Buyer,
                'event_type' => DealEventEventType::Purchase,
            ]);
            $dealEvent->deal_eventable()->associate($buyer);
            $deal->dealEvents()->save($dealEvent);

            return $deal->fresh();
        });

        return $deal;
    }

    /*
     * 商品出品キャンセル
     * 
     * @param Deal $deal
     * @param User $seller
     * @exception InvalidStatusTransitionException
     * @return Deal
     */
    public function cancel(Deal $deal, User $seller): Deal
    {
        if (!in_array($deal->status, [DealStatus::Listing])) {
            throw new InvalidStatusTransitionException();
        }

        $deal = DB::transaction(function () use ($deal, $seller) {
            $deal->update(['status' => DealStatus::Canceled]);

            $dealEvent = new DealEvent([
                'actor_type' => DealEventActorType::Seller,
                'event_type' => DealEventEventType::Cancel,
            ]);
            $dealEvent->deal_eventable()->associate($seller);
            $deal->dealEvents()->save($dealEvent);

            return $deal->fresh();
        });

        return $deal;
    }

    /*
     * 配送報告
     * 
     * @param Deal $deal
     * @param User $seller
     * @exception InvalidStatusTransitionException
     * @return Deal
     */
    public function reportDelivery(Deal $deal, User $seller): Deal
    {
        if (!in_array($deal->status, [DealStatus::Purchased])) {
            throw new InvalidStatusTransitionException();
        }
        
        $deal = DB::transaction(function () use ($deal, $seller) {
            $deal->update(['status' => DealStatus::Shipping]);

            $dealEvent = new DealEvent([
                'actor_type' => DealEventActorType::Seller,
                'event_type' => DealEventEventType::ReportDelivery,
            ]);
            $dealEvent->deal_eventable()->associate($seller);
            $deal->dealEvents()->save($dealEvent);

            return $deal->fresh();
        });

        return $deal;
    }

    /*
     * 受取報告
     * 
     * @param Deal $deal
     * @param User $buyer
     * @exception InvalidStatusTransitionException
     * @return Deal
     */
    public function reportReceipt(Deal $deal, User $buyer): Deal
    {
        if (!in_array($deal->status, [DealStatus::Shipping])) {
            throw new InvalidStatusTransitionException();
        }
        
        $deal = DB::transaction(function () use ($deal, $buyer) {
            $deal->update(['status' => DealStatus::Completed]);

            $dealEvent = new DealEvent([
                'actor_type' => DealEventActorType::Buyer,
                'event_type' => DealEventEventType::ReportReceipt,
            ]);
            $dealEvent->deal_eventable()->associate($buyer);
            $deal->dealEvents()->save($dealEvent);

            return $deal->fresh();
        });

        return $deal;
    }
}
