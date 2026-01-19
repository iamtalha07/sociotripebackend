<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use App\Models\UserCard;
use Illuminate\Http\Request;
use App\Models\BoostActivity;
use App\Models\BoostingTargetCity;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Responses\BaseResponse;
use App\Http\Services\StripeService;

class BoostController extends Controller
{
    private $currentUser;

    function __construct()
    {
        $this->currentUser = auth('api')->user();
    }

    public function getActivityToBoost()
    {
        $user = $this->currentUser;

        $activities = Activity::with('pricings:id,activity_id,category_name,price')
            ->where('user_id', $user->id)
            ->whereDoesntHave('boostActivities', function ($query) {
                $query->where('status', 'active'); // 🚫 exclude active boosts
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $response = $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'title' => $activity->title,
                'description' => $activity->description,
                'city' => $activity->city,
                'state' => $activity->state,
                'pricings' => $activity->pricings->map(function ($pricing) {
                    return [
                        'id' => $pricing->id,
                        'category_name' => $pricing->category_name,
                        'price' => $pricing->price,
                    ];
                }),
            ];
        });

        return new BaseResponse(
            STATUS_CODE_OK,
            STATUS_CODE_OK,
            'Activities fetched successfully',
            $response
        );
    }

    public function boostActivity(Request $request)
    {
        $user = $this->currentUser;


        $request->validate([
            'activity_id' => 'required|exists:activities,id',
            'duration' => 'required|integer|min:1',
            'budget_per_day' => 'required|numeric|min:0',
            'country_id' => 'nullable|exists:countries,id',
            'target_city_id' => 'required|array|min:1',
            'target_city_id.*' => 'exists:cities,id',
            'boosting_start_date' => 'required|date',
            'boosting_end_date' => 'required|date|after:boosting_start_date',
            'amount' => 'required',
        ]);

        DB::beginTransaction();

        try {
            $paymentType = $request->payment_type ?? 'stripe';
            // Create boost activity
            $boostActivity = BoostActivity::create([
                'user_id' => $user->id,
                'activity_id' => $request->activity_id,
                'duration' => $request->duration,
                'budget_per_day' => $request->budget_per_day,
                'country_id' => $request->country_id,
                'boosting_start_date' => $request->boosting_start_date,
                'boosting_end_date' => $request->boosting_end_date,
                'amount' => $request->amount,
                'card_id' => $request->card_id ?? null,
                'status' => 'active',
            ]);

            // Insert target cities
            foreach ($request->target_city_id as $cityId) {
                BoostingTargetCity::create([
                    'boost_activity_id' => $boostActivity->id,
                    'city_id' => $cityId,
                ]);
            }

            if ($paymentType == 'stripe') {
                if (is_null($user->stripe_token)) {
                    return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'No payment method found. Please add a card.');
                }

                $stripe = new StripeService();

                $userCard = UserCard::where('user_id', $user->id)
                    ->where('card_id', $request->card_id)
                    ->first();

                if (!$userCard) {
                    return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'No card found. Please add a card.');
                }

                $charge = $stripe::chargeIntentAmount(
                    $request->amount,
                    $user->stripe_token,
                    $request->card_id ?? $userCard->card_id,
                    'Boost Activity Purchase'
                );
            }

            DB::commit();

            return new BaseResponse(
                STATUS_CODE_OK,
                STATUS_CODE_OK,
                'Activity boosted successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return new BaseResponse(
                STATUS_CODE_BADREQUEST,
                STATUS_CODE_BADREQUEST,
                $e->getMessage()
            );
        }
    }

    public function boostDashboard(Request $request)
    {
        $user = $this->currentUser;

        $boostActivities = BoostActivity::with([
            'activity:id,title,cover_image',
            'country:id,name',
            'targetCities.city:id,name'
        ])
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status');

        $activityResponse = [
            'active' => $boostActivities->get('active', collect())->map(function ($boost) {
                return [
                    'id' => $boost->id,
                    'duration' => $boost->duration,
                    'budget_per_day' => $boost->budget_per_day,
                    'country' => $boost->country?->name,
                    'cities' => $boost->targetCities->map(fn($c) => $c->city?->name),
                    'activity' => [
                        'id' => $boost->activity?->id,
                        'title' => $boost->activity?->title,
                        'cover_image' => $boost->activity?->cover_image,
                    ],
                ];
            }),

            'completed' => $boostActivities->get('completed', collect())->map(function ($boost) {
                return [
                    'id' => $boost->id,
                    'duration' => $boost->duration,
                    'budget_per_day' => $boost->budget_per_day,
                    'country' => $boost->country?->name,
                    'cities' => $boost->targetCities->map(fn($c) => $c->city?->name),
                    'activity' => [
                        'title' => $boost->activity?->title,
                        'cover_image' => $boost->activity?->cover_image,
                    ],
                ];
            }),
        ];

        $response = [
            'activities' => $activityResponse,
            'posts' => null
        ];

        return new BaseResponse(
            STATUS_CODE_OK,
            STATUS_CODE_OK,
            'Boost dashboard fetched successfully',
            $response
        );
    }

    public function endBoostActivity(Request $request)
    {
        $request->validate([
            'boost_activity_id' => 'required|exists:boost_activities,id',
        ]);

        $user = $this->currentUser;

        $boostActivity = BoostActivity::where('id', $request->boost_activity_id)
            ->where('user_id', $user->id) // ensure the user owns this boost
            ->first();

        if (!$boostActivity) {
            return new BaseResponse(
                STATUS_CODE_BADREQUEST,
                STATUS_CODE_BADREQUEST,
                'Boost activity not found'
            );
        }

        $boostActivity->update([
            'status' => 'ended'
        ]);

        return new BaseResponse(
            STATUS_CODE_OK,
            STATUS_CODE_OK,
            'Boost activity ended successfully'
        );
    }
}
