<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'booking_type' => 'required|in:direct_booking,on_request',
            'street_address' => 'required|string',
            'apartment_floor' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'pricing' => 'nullable|array|min:1',
            'pricing.*.category_name' => 'nullable|string',
            'pricing.*.age_min' => 'nullable|integer',
            'pricing.*.age_max' => 'nullable|integer',
            'pricing.*.price' => 'nullable|numeric|min:0',
            'additional_services' => 'nullable|array',
            'additional_services.*.service_name' => 'required|string',
            'additional_services.*.price' => 'required|numeric|min:0',
            'working_hours' => 'required|array|min:1',
            'working_hours.*.day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'working_hours.*.start_time' => 'required|date_format:H:i',
            'working_hours.*.end_time' => 'required|date_format:H:i|after:working_hours.*.start_time',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
