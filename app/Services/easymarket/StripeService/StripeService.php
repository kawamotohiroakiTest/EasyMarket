<?php

namespace App\Services\easymarket\StripeService;

use App\Models\Product;
use App\Models\User;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeService implements StripeServiceInterface
{
    public function createPaymentIntent(Product $product, User $buyer): PaymentIntent
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $product->price,
            'currency' => 'jpy',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        return $paymentIntent;
    }

    public function verifyPaymentIntent(string $paymentIntentId): bool
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

        return $paymentIntent->status === 'succeeded';
    }
}
