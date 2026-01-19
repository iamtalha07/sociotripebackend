<?php

namespace App\Http\Controllers\Api;

use Log;
use Stripe\Stripe;
use App\Models\UserCard;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Responses\BaseResponse;
use App\Http\Services\StripeService;
use App\Http\Requests\AddCardRequest;

class CardController extends Controller
{
    public $currentUser;

    public function __construct()
    {
        $this->currentUser = auth('api')->user();
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function addCard(AddCardRequest $request)
    {
        DB::beginTransaction();

        try {
            $stripe = new StripeService();
            $stripe = new StripeClient(env('STRIPE_SECRET'));

            if (!$this->currentUser->stripe_token) {
                $customer = $stripe->customers->create([
                    'name'  => $this->currentUser->first_name,
                    'email' => $this->currentUser->email,
                    'phone' => $this->currentUser->phone,
                ]);
                $this->currentUser->stripe_token = $customer->id;
                $this->currentUser->save();
            }

            // ✅ Create Payment Method from raw card details
            $paymentMethod = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number'    => $request->card_number,
                    'exp_month' => $request->expiry_month,
                    'exp_year'  => $request->expiry_year,
                    'cvc'       => $request->cvc,
                ],
                'billing_details' => [
                    'name' => $request->card_holder_name,
                ],
            ]);

            $paymentMethodId = $paymentMethod->id;
            // $paymentMethodId = $request->payment_method_id;

            $stripeCard = $stripe->paymentMethods->attach(
                $paymentMethodId,
                ['customer' => $this->currentUser->stripe_token]
            );

            // Set Default Payment Method
            $stripe->customers->update($this->currentUser->stripe_token, [
                'invoice_settings' => ['default_payment_method' => $paymentMethodId],
            ]);

            $paymentMethod = $stripe->paymentMethods->retrieve($paymentMethodId);

            UserCard::where('user_id', $this->currentUser->id)
                ->update(['is_default' => 0]);

            UserCard::insert([
                'user_id'          =>  $this->currentUser->id,
                'card_holder'      =>  $this->currentUser->first_name . ' ' .  $this->currentUser->last_name,
                'card_id' => $paymentMethodId,
                'last_four_digits' => $paymentMethod->card->last4,
                'type'              => $paymentMethod->card->brand === 'visa' ? 'visa' : 'master',
                'is_default'       => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            if ($stripeCard) {
                DB::commit();

                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Successfully added card.');
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::info("notifi error 2 " . $e->getMessage());
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, 'Unable to add card. Please check your card details and try again.');
        } catch (\Exception $e) {
            \Log::info("notifi error 2 " . $e->getMessage());
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, 'Something went wrong. Please try again later.');
        }
    }

    public function listCard()
    {
        $cards = UserCard::where('user_id', $this->currentUser->id)
            ->get()
            ->map(function ($card) {
                $card->card_image = match (strtolower($card->type)) {
                    'visa'             => url('/cardImg/visa.png'),
                    'master', 'mastercard' => url('/cardImg/mastercard.png'),
                    'amex', 'american-express' => url('/cardImg/american-express.png'),
                    'discover'         => url('/cardImg/discover.png'),
                    default            => null,
                };
                return $card;
            });

        if ($cards->isNotEmpty()) {
            return new BaseResponse(
                STATUS_CODE_OK,
                STATUS_CODE_OK,
                'Card list fetched successfully.',
                $cards
            );
        }

        return new BaseResponse(
            STATUS_CODE_OK,
            STATUS_CODE_OK,
            'No card found.'
        );
    }
}
