<?php

return [
    'plans' => [
        'monthly' => [
            'name' => 'Monthly',
            'price_id' => env('STRIPE_PRICE_MONTHLY', 'price_monthly'),
            'description' => 'Billed monthly',
            'amount' => '700 tk / month',
        ],
        'annual' => [
            'name' => 'Annual',
            'price_id' => env('STRIPE_PRICE_ANNUAL', 'price_annual'),
            'description' => 'Billed yearly with a discount',
            'amount' => '7000 tk / year',
        ],
    ],

    'gateways' => [
        'stripe' => [
            'name' => 'Stripe (Credit Card)',
            'description' => 'Pay with credit card through Stripe.',
            'requires_card' => true,
        ],
        'sslcommerz' => [
            'name' => 'SSLCommerz',
            'description' => 'Pay with SSLCommerz via mobile number or bank transfer.',
            'instructions' => 'Please use SSLCommerz to complete your payment. Once the payment is complete, provide the transaction ID to support.',
        ],
        'bkash' => [
            'name' => 'bKash',
            'description' => 'Pay using bKash mobile wallet.',
            'instructions' => 'Send the payment through bKash and note the transaction ID. Then contact support with the payment details.',
        ],
        'nagad' => [
            'name' => 'Nagad',
            'description' => 'Pay using Nagad mobile wallet.',
            'instructions' => 'Complete your payment through Nagad and share the transaction reference with the admin team.',
        ],
        'rocket' => [
            'name' => 'Rocket',
            'description' => 'Pay using Rocket mobile wallet.',
            'instructions' => 'Use Rocket to make payment and submit the transaction confirmation to support.',
        ],
        'bank' => [
            'name' => 'Bank Transfer',
            'description' => 'Pay manually via bank transfer.',
            'instructions' => 'Transfer the amount to our bank account and provide the receipt or transaction details to support.',
        ],
    ],
];
