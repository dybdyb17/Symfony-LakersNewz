<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeService
{
    public function __construct(private string $secretKey)
    {
        Stripe::setApiKey($this->secretKey);
    }

    public function createCheckoutSession(float $montant, string $successUrl, string $cancelUrl): Session
    {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Dépôt LakersNewz',
                    ],
                    'unit_amount' => (int) ($montant * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }

    public function getSession(string $sessionId): Session
    {
        return Session::retrieve($sessionId);
    }
}
