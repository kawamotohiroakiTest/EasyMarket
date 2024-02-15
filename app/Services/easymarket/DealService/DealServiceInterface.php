<?php

namespace App\Services\easymarket\DealService;

use App\Models\Deal;
use App\Models\User;
use Stripe\PaymentIntent;

interface DealServiceInterface
{
    public function createPaymentIntent(Deal $deal, User $buyer): PaymentIntent;
    public function verifyPaymentIntent(Deal $deal, User $buyer, string $paymentIntentId): Deal;
}