<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddActivityRequest;
use App\Http\Responses\BaseResponse;
use App\Models\Activity;
use App\Models\ActivityPricing;
use App\Models\ActivityAdditionalService;
use App\Models\ActivityWorkingHour;
use App\Models\ActivityImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{

    private $currentUser;

    function __construct()
    {
        $this->currentUser = auth('api')->user();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddActivityRequest $request)
    {
        try {
            DB::beginTransaction();

            // Handle cover image uploads
            if ($request->hasFile('cover_image')) {
                $coverImage = $request->file('cover_image');
                $coverImagePath = uploadImage($coverImage, 'activities_cover_image', null);
            }

            // Create activity
            $activity = Activity::create([
                'user_id' => $this->currentUser->id,
                'title' => $request->title,
                'description' => $request->description,
                'booking_type' => $request->booking_type,
                'street_address' => $request->street_address,
                'apartment_floor' => $request->apartment_floor,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'cover_image' => $coverImagePath ?? null,
                'status' => $request->status,
            ]);

            // Create pricing categories
            foreach ($request->pricing as $pricing) {
                ActivityPricing::create([
                    'activity_id' => $activity->id,
                    'category_name' => $pricing['category_name'],
                    'age_min' => $pricing['age_min'] ?? null,
                    'age_max' => $pricing['age_max'] ?? null,
                    'price' => $pricing['price'],
                ]);
            }

            // Create additional services
            if ($request->has('additional_services')) {
                foreach ($request->additional_services as $service) {
                    ActivityAdditionalService::create([
                        'activity_id' => $activity->id,
                        'service_name' => $service['service_name'],
                        'price' => $service['price'],
                    ]);
                }
            }

            // Create working hours
            foreach ($request->working_hours as $workingHour) {
                ActivityWorkingHour::create([
                    'activity_id' => $activity->id,
                    'day' => $workingHour['day'],
                    'start_time' => $workingHour['start_time'],
                    'end_time' => $workingHour['end_time'],
                ]);
            }

            // Attach categories
            $activity->categories()->attach($request->category_ids);

            // Attach amenities
            if ($request->has('amenity_ids')) {
                $activity->amenities()->attach($request->amenity_ids);
            }

            // Handle image uploads
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                foreach ($images as $index => $image) {
                    $path = uploadImage($image, 'activities', null);
                    ActivityImage::create([
                        'activity_id' => $activity->id,
                        'image_path' => $path,
                        'is_primary' => $index === 0, // First image is primary
                    ]);
                }
            }

            DB::commit();

            // Load relationships
            $activity->load(['pricings', 'additionalServices', 'workingHours', 'categories', 'amenities', 'images']);

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Activity created successfully', $activity);
        } catch (\Exception $e) {
            DB::rollBack();
            return new BaseResponse(STATUS_CODE_ERROR, STATUS_CODE_ERROR, 'Failed to create activity: ' . $e->getMessage());
        }
    }

    public function getActivities(Request $request)
    {
        $activities = Activity::where('user_id', $this->currentUser->id)->get();
        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, 'Activities retrieved successfully', $activities);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
