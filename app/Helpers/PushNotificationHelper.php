<?php

use App\Models\User;
use Illuminate\Support\Arr;
use App\Events\NotificationEvent;
use Google\Client as GoogleClient;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;

if (!function_exists('sendPushNotification')) {
    function sendPushNotification($title, $message, $tokens = [], $extra = [], $isGenericNotification = true, $priority = false)
    {
        foreach ($tokens as $receiverId => $token) {
            $user = User::find($receiverId);
            if ($user && $isGenericNotification) {
                // Dispatch the notification event with correct data for each user
                event(new NotificationEvent(
                    Arr::get($extra, 'sender_id', []),
                    $receiverId,
                    $message,
                    Arr::get($extra, 'notify_user_type', []),
                    Arr::get($extra, 'other_user_type', []),
                    $extra,
                    $title,
                    'App'
                ));
            }

            // $settingsArray = $user->settings;
            // $getPushNotificationSetting = Arr::get($settingsArray, 'push_notification', []);
            // if ($getPushNotificationSetting == 1 && $token) {

            //     $credentialsFilePath = base_path('fcm.json');
            //     $client = new GoogleClient();
            //     $client->setAuthConfig($credentialsFilePath);
            //     $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

            //     try {
            //         $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

            //         $projectID = config('services.firebase.project_id');

            //         $apiUrl = "https://fcm.googleapis.com/v1/projects/{$projectID}/messages:send";
            //         $data = array_map('strval', $extra);


            //         $message = [
            //             'message' => [
            //                 'token' => $token,
            //                 'notification' => [
            //                     'title' => $title,
            //                     'body' => $message,
            //                 ],
            //                 'data' => $data,

            //                 'android' => [
            //                     'priority' => 'high', //normal
            //                     'notification' => [
            //                         'sound' => 'default',
            //                     ],
            //                 ],

            //                 'apns' => [
            //                     'headers' => [
            //                         'apns-priority' => '10',
            //                     ],
            //                     'payload' => [
            //                         'aps' => [
            //                             'alert' => [
            //                                 'title' => $title,
            //                                 'body' => $message,
            //                             ],
            //                             'sound' => 'default',
            //                             'content-available' => 1,
            //                         ],
            //                     ],
            //                 ],
            //             ],
            //         ];

            //         $httpClient = new HttpClient();

            //         $response = $httpClient->post($apiUrl, [
            //             'headers' => [
            //                 'Authorization' => 'Bearer ' . $accessToken,
            //                 'Content-Type' => 'application/json',
            //             ],
            //             'json' => $message,
            //         ]);

            //         \Log::info("Success " . $response->getBody()->getContents());
            //         return response()->json([
            //             'response' => json_decode($response->getBody()->getContents(), true),
            //         ]);
            //     } catch (RequestException $e) {
            //         \Log::info("notifi error 1 " . $e->getMessage());
            //         \Log::error("Notification error: " . $e->getResponse()->getBody()->getContents());
            //         return response()->json([
            //             'error' => $e->getMessage(),
            //         ], $e->getCode());
            //     } catch (\Exception $e) {
            //         \Log::info("notifi error 2 " . $e->getMessage());
            //         return response()->json([
            //             'error' => $e->getMessage(),
            //         ], $e->getCode());
            //     }
            // }
        }
    }
}
