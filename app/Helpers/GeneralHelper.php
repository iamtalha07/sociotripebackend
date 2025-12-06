<?php

use \Illuminate\Support\Facades\File;
// use Twilio\Rest\Client;

if (! function_exists('uploadImage')) {

    function uploadImage($file, $path, $oldFilePath = null)
    {
        $prefixFolder = 'uploads';
        if (File::exists(public_path($oldFilePath))) {
            File::delete(public_path($oldFilePath));
        }
        $filename = md5(time() . rand(1000, 9999)) . '.' . $file->extension();

        // File upload location
        $location = $prefixFolder . '/' . $path;

        // Upload file
        $file->move($location, $filename);
        return $prefixFolder . '/' . $path . '/' . $filename;
    }
}

if (! function_exists('uploadAdminImage')) {

    function uploadAdminImage($file, $folder, $oldFile = null)
    {

        if ($oldFile && file_exists(public_path('uploads/' . $folder . '/' . $oldFile))) {
            unlink(public_path('uploads/' . $folder . '/' . $oldFile));
        }

        $filename = md5(time() . rand(1000, 9999)) . '.' . $file->getClientOriginalExtension();

        $file->storeAs($folder, $filename, 'public');

        return 'uploads/' . $folder . '/' . $filename;
    }
}

if (! function_exists('sendPushNotification')) {
    function sendNotification($currentUser, $otherUsers, $message, $title, $notificationType = '')
    {
        if (!is_array($otherUsers)) {
            $otherUsers = [$otherUsers];
        }

        foreach ($otherUsers as $otherUser) {
            $extras = [
                'notification_type' => $notificationType,
                'message' => $message,
                'sender_id' => $currentUser->id,
                'notify_user_type' => $currentUser->role_id,
                'other_user_type' => $otherUser->role_id,
                'is_approved_by_admin' => $otherUser->is_verified_by_admin,
            ];

            if (!empty($otherUser->fcm_token)) {
                $tokens = [$otherUser->id => $otherUser->fcm_token];

                sendPushNotification(
                    $title,
                    $message,
                    $tokens,
                    $extras,
                    true
                );
            }
        }
    }
}


// if (! function_exists('sendSMS')) {
//     function sendSMS($to, $message)
//     {
//         $client = new Client(
//             config('services.twilio.twilio_account_sid'),
//             config('services.twilio.twilio_auth_token')
//         );

//         return $client->messages->create(
//             $to,
//             [
//                 'from' => config('services.twilio.twilio_number'),
//                 'body' => $message
//             ]
//         );
//     }
// }

