<?php

namespace App\Services\easymarket\StripeService;

use App\Models\Product;
use App\Models\User;
use Stripe\PaymentIntent;

interface StripeServiceInterface
{
    public function createPaymentIntent(Product $product, User $buyer): PaymentIntent;
    public function verifyPaymentIntent(string $paymentIntentId): bool;
}