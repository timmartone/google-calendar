<?php

namespace tmartone\LaravelGoogleCalendar\Http\Middleware;


use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use tmartone\LaravelGoogleCalendar\GoogleAuth;

class GoogleAuthConsent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $profile = config('google-calendar.default_auth_profile');
        if($profile == 'oauth_consent')
        {
            $user = auth()->user();
            if(!$user->google_auth)
            {
                $client = GoogleAuth::index();
                if(gettype($client) == "array")
                    return Redirect::to($client['url']);
            }
        }
        return $next($request);
    }
}
