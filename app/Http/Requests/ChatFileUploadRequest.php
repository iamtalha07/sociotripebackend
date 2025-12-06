<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatFileUploadRequest extends FormRequest
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
            'file' => 'required|file',
            'sender_id' => 'required',
            'sender_model' => 'required',
            'receiver_id' => 'required',
            'receiver_model' => 'required',
            'type' => 'required|gte:2|lte:5',
            'chat_id' => 'nullable',
        ];
    }
}
