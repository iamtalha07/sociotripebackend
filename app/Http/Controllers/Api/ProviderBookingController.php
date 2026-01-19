<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\ActivityWorkingHour;
use App\Http\Controllers\Controller;
use App\Http\Responses\BaseResponse;

class ProviderBookingController extends Controller
{
    private $currentUser;

    function __construct()
    {
        $this->currentUser = auth('api')->user();
    }

    public function getBookings(Request $request)
    {
        $provider = $this->currentUser;

        $status = $request->status ?? 'active';
        $date   = $request->date; // expected format: YYYY-MM-DD

        $bookings = Booking::with([
            'user:id,first_name,last_name,email,image',
            'activity',
            'details'
        ])
            ->where('provider_id', $provider->id)
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($date, function ($query) use ($date) {
                $query->where('booking_date', $date);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $response = $bookings->map(function ($booking) {

            $totalPersons = $booking->details
                ->where('model', 'activity_pricings')
                ->sum('quantity');

            $workingHour = ActivityWorkingHour::where('activity_id', $booking->activity_id)
                ->where('day', $booking->booking_day)
                ->first();

            return [
                'user' => [
                    'id' => $booking->user?->id,
                    'first_name' => $booking->user?->first_name,
                    'last_name' => $booking->user?->last_name,
                    'image' => $booking->user?->image,
                ],
                'activity' => [
                    'id' => $booking->activity?->id,
                    'title' => $booking->activity?->title,
                    'image' => $booking->activity?->cover_image,
                ],
                'booking_date' => $booking->booking_date,
                'start_time' => $workingHour?->start_time,
                'end_time' => $workingHour?->end_time,
                'total_person' => $totalPersons ?? 0,
                'booking_price' => $booking->total_price,
                'status' => $booking->status,
            ];
        });

        return new BaseResponse(
            STATUS_CODE_OK,
            STATUS_CODE_OK,
            'Bookings fetched successfully',
            $response
        );
    }

    public function getBookingDetail(Booking $booking)
    {
        // Load required relations
        $booking->load([
            'user:id,first_name,last_name,image',
            'activity.pricings', // eager load pricings
            'details'
        ]);

        // Total persons (only activity_pricings)
        $totalPersons = $booking->details
            ->where('model', 'activity_pricings')
            ->sum('quantity');

        // Fetch working hours using activity_id + booking_day
        $workingHour = ActivityWorkingHour::where('activity_id', $booking->activity_id)
            ->where('day', $booking->booking_day)
            ->first();

        $response = [
            'user' => [
                'id' => $booking->user?->id,
                'first_name' => $booking->user?->first_name,
                'last_name' => $booking->user?->last_name,
                'image' => $booking->user?->image,
            ],
            'activity' => [
                'id' => $booking->activity?->id,
                'title' => $booking->activity?->title,
                'cover_image' => $booking->activity?->cover_image,
                'city' => $booking->activity?->city,
                'state' => $booking->activity?->state,
                'description' => $booking->activity?->description,
                'pricings' => $booking->activity?->pricings->map(function ($pricing) {
                    return [
                        'id' => $pricing->id,
                        'category_name' => $pricing->category_name,
                        'age_min' => $pricing->age_min,
                        'age_max' => $pricing->age_max,
                        'price' => $pricing->price,
                    ];
                })
            ],
            'booking' => [
                'booking_date' => $booking->booking_date,
                'total_person' => $totalPersons ?? 0,
                'start_time' => $workingHour?->start_time,
                'end_time' => $workingHour?->end_time,
            ]
        ];

        return new BaseResponse(
            STATUS_CODE_OK,
            STATUS_CODE_OK,
            'Booking detail fetched successfully',
            $response
        );
    }
}
