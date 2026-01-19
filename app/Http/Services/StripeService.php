<?php

namespace App\Http\Services;

use Stripe\Stripe;
use Illuminate\Support\Str;

class StripeService
{
    public static function createCustomer($customerData, $isException = true)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        return \Stripe\Customer::create($customerData);
    }

    public static function updateCustomer($customerId, $customerData)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $customer = \Stripe\Customer::update($customerId, $customerData);
        return $customer;
    }

    public static function cardToken($cardData)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $token = \Stripe\Token::create([
            'card' => $cardData,
        ]);

        return $token;
    }

    public static function addCardSource($user_stripe_token, $cardToken)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        return \Stripe\Customer::createSource($user_stripe_token, [
            'source' => $cardToken,
        ]);
    }

    public static function getStripeCard($user_stripe_token, $cardToken)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        return \Stripe\Customer::retrieveSource($user_stripe_token, $cardToken);
    }

    public static function chargeAmount($amount, $sourceToken, $description = "N/A")
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $charge = \Stripe\Charge::create([
                "amount" => $amount * 100,
                "currency" => "usd",
                "customer" => $sourceToken,
                "description" => $description,
            ]);
            return $charge;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        } catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $error = $e->getError();
            $errorMessage = $error['message'];
            throw new \Exception($errorMessage);
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            throw new \Exception('Too many requests made to the API too quickly.');
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            throw new \Exception('Invalid parameters were supplied to Stripe\'s API.');
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            throw new \Exception('Authentication with Stripe\'s API failed.');
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            throw new \Exception('Network communication with Stripe failed.');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            throw new \Exception('An error occurred while processing your payment.');
        }
    }

    public static function chargeIntentAmount($amount, $sourceToken, $paymentMethod, $description = 'N/A')
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $charge = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'customer' => $sourceToken,
                'description' => $description,
                'payment_method' => $paymentMethod,
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
            ]);

            return $charge;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        } catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $error = $e->getError();
            $errorMessage = $error['message'];
            throw new \Exception($errorMessage);
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            throw new \Exception('Too many requests made to the API too quickly.');
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            throw new \Exception('Invalid parameters were supplied to Stripe\'s API.');
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            throw new \Exception('Authentication with Stripe\'s API failed.');
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            throw new \Exception('Network communication with Stripe failed.');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            throw new \Exception('An error occurred while processing your payment.');
        }
    }

    public static function chargeIntentAmountSeller($amount, $adminFee, $sourceToken, $paymentMethod, $accountId, $description = 'N/A')
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $charge = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'customer' => $sourceToken, // This must be stripe_customer_id
                'payment_method' => $paymentMethod,
                'confirm' => true,
                'application_fee_amount' => $adminFee * 100, // admin's cut
                'transfer_data' => [
                    'destination' => $accountId,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
            ]);

            return $charge;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        } catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $error = $e->getError();
            $errorMessage = $error['message'];
            throw new \Exception($errorMessage);
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            throw new \Exception('Too many requests made to the API too quickly.');
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            throw new \Exception('Invalid parameters were supplied to Stripe\'s API.');
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            throw new \Exception('Authentication with Stripe\'s API failed.');
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            throw new \Exception('Network communication with Stripe failed.');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            throw new \Exception('An error occurred while processing your payment.');
        }
    }

    public static function createStipePrice($poductId, $packagePrice, $validity)
    {
        return \Stripe\Price::create([
            'unit_amount' => $packagePrice * 100, // Stripe expects amount in cents
            'currency' => 'usd',
            'recurring' => [
                'interval' => Str::lower($validity) == 'monthly' ? 'month' : 'year', // day, week, month, year
            ],
            'product' => $poductId,
        ]);
    }

    // Cancel subscription
    public static function cancelSubscription($subscriptionId)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $subscription = \Stripe\Subscription::update($subscriptionId, [
            'cancel_at_period_end' => true
        ]);
        return $subscription;
    }

    // Cancel subscription
    public static function retrieveSubscription($subscriptionId)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        return \Stripe\Subscription::retrieve($subscriptionId);
    }
}
