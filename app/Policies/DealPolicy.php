<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DealPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * 商品購入API
     * 
     * @param User $user
     * @param Deal $deal
     * @return bool
     */
    public function purchaseDeal(User $user, Deal $deal): bool
    {
        // 出品者が自分ではない場合のみアクセス可
        return $user->id !== $deal->seller_id;
    }

    /**
     * 商品出品キャンセルAPI
     * 
     * @param User $user
     * @param Deal $deal
     * @return bool
     */
    public function cancelDeal(User $user, Deal $deal): bool
    {
        // 出品者が自分である場合のみアクセス可
        return $user->id === $deal->seller_id;
    }

    /**
     * 配送報告API
     * 
     * @param User $user
     * @param Deal $deal
     * @return bool
     */
    public function reportDeliveryDeal(User $user, Deal $deal): bool
    {
        // 出品者が自分である場合のみアクセス可
        return $user->id === $deal->seller_id;
    }

    /**
     * 受取報告API
     * 
     * @param User $user
     * @param Deal $deal
     * @return bool
     */
    public function reportReceiptDeal(User $user, Deal $deal): bool
    {
        // 購入者が自分である場合のみアクセス可
        return $user->id === $deal->buyer_id;
    }
}
