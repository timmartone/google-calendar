<?php

namespace tmartone\LaravelGoogleCalendar;

use URL;
use DateTime;
use Exception;
use Carbon\Carbon;
use Google_Client;
use App\Models\User;
use Carbon\CarbonInterface;
use Google_Service_Calendar;
use Illuminate\Http\Request;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_Events;
use Illuminate\Support\Facades\Redirect;

class GoogleAuth
{
    public static function index(Request $request = null, $user_id = 1)
    {
        $client = new Google_Client();
        $client->setApplicationName(config('google-calendar')['auth_profiles']['oauth_consent']['google_app_name']);
        $client->setClientId(config('google-calendar')['auth_profiles']['oauth_consent']['google_client_id']);
        $client->setClientSecret(config('google-calendar')['auth_profiles']['oauth_consent']['google_client_secret']);
        $client->setRedirectUri(URL::to('/') . '/oauth2callback');
        $client->setScopes(Google_Service_Calendar::CALENDAR);
        $client->setAccessType("offline");
        $client->setPrompt('select_account consent');
        $user = $user_id?User::findOrFail($user_id):auth()->user();
        $accessToken = ($request && $request->code) ? $client->fetchAccessTokenWithAuthCode($request->code) : json_decode($user->google_auth, true);

        if($accessToken) {
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
            $client->setAccessToken($accessToken);

            if ($client->isAccessTokenExpired()) {
                // Refresh the token if possible, else fetch a new one.
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                } else {
                    $auth_url = $client->createAuthUrl();
                    return response()->json([
                        "message" => "You must connect your google account.",
                        "url" => $auth_url
                ], 202);
                }
            }
        } else {
            $auth_url = $client->createAuthUrl();
                    return [
                        "message" => "You must connect your google account.",
                        "url" => $auth_url
                ];
        }
        $user->google_auth = json_encode($client->getAccessToken());
        $user->save();

        return $client;
    }

}
