<?php

namespace tmartone\LaravelGoogleCalendar\Http\Controllers;

use URL;
use Exception;
use Carbon\Carbon;
use Google_Client;
use App\Models\User;
use Google_Service_Calendar;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use tmartone\LaravelGoogleCalendar\Event;
use tmartone\LaravelGoogleCalendar\GoogleCalendar;
use tmartone\LaravelGoogleCalendar\GoogleAuth;

class OAuthController extends Controller
{
    public function events(){
        $events = Event::get();
        return response()->json($events, 200);
    }

    public function index(Request $request)
    {
        $client = GoogleAuth::index($request);
        return  response()->json(["message" => "succss"], 403);
    }

}
