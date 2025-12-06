<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileSetupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guard('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'user_image' => 'nullable|array',
            // 'user_image.*.image' => 'nullable|file|image|mimes:jpg,jpeg,png|max:2048',
            // 'user_image.*.is_main' => 'nullable|in:0,1',
            'bio' => 'nullable|string',
            'location' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'gender' => 'nullable|in:man,woman,transgender-man,transgender-woman,non-binary,neuter,common-gender,none',
            'weight' => 'nullable|string',
            'Height' => 'nullable|string',
            'dob' => 'nullable|date',
            'profession' => 'nullable|string',
            'field_of_job' => 'nullable|string',
            'relationship_type' => 'nullable|in:serious-relationship,casual-dating,friendship',
            'education' => 'nullable|string',
            'religion' => 'nullable|string',
            'is_notification' => 'nullable|in:0,1',
            'is_location' => 'nullable|in:0,1',
            'is_emergency_contact' => 'nullable|in:0,1',
        ];
    }
}
