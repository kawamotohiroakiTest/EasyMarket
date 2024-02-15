<?php

namespace App\Services\easymarket\StripeService;

use App\Models\Product;
use App\Models\User;
use Stripe\PaymentIntent;

class StripeServiceMock implements StripeServiceInterface
{
    public function createPaymentIntent(Product $product, User $buyer): PaymentIntent
    {
        // PaymentIntentをモックするために固定の値を返す
        return (new PaymentIntent())::constructFrom([
            'id' => 'pi_123',
            'amount' => $product->price,
            'currency' => 'jpy',
            'status' => 'requires_payment_method',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'client_secret' => 'pi_123_secret_123',
        ]);
    }

    public function verifyPaymentIntent(string $paymentIntentId): bool
    {
        // 決済が成功したことを示すためにtrueを返す
        return true;
    }
}
