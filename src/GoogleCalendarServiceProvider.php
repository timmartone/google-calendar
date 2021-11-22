<?php

namespace tmartone\LaravelGoogleCalendar;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Redirect;
use tmartone\LaravelGoogleCalendar\GoogleAuth;
use tmartone\LaravelGoogleCalendar\Exceptions\InvalidConfiguration;

class GoogleCalendarServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/google-calendar.php' => config_path('google-calendar.php'),
        ], 'config');

        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/google-calendar.php', 'google-calendar');
        $this->app->bind(GoogleCalendar::class, function () {
            $config = config('google-calendar');
            $this->guardAgainstInvalidConfiguration($config);

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
            
            return GoogleCalendarFactory::createForCalendarId($config['default_calendar_id']);
        });

        $this->app->register('tmartone\LaravelGoogleCalendar');

        $this->app->router->aliasMiddleware('google_consent', \tmartone\LaravelGoogleCalendar\Http\Middleware\GoogleAuthConsent::class);

        $this->app->alias(GoogleCalendar::class, 'laravel-google-calendar');
    }

    protected function guardAgainstInvalidConfiguration(array $config = null)
    {

        $authProfile = $config['default_auth_profile'];

        if($authProfile === 'oauth_consent')
        {
            $this->validateOAuthConsentConfigSettings($config);

            return;
        }

        if (empty($config['calendar_id'])) {
            throw InvalidConfiguration::calendarIdNotSpecified();
        }

        if ($authProfile === 'service_account') {
            $this->validateServiceAccountConfigSettings($config);

            return;
        }

        if ($authProfile === 'oauth') {
            $this->validateOAuthConfigSettings($config);

            return;
        }

        throw InvalidConfiguration::invalidAuthenticationProfile($authProfile);
    }

    protected function validateServiceAccountConfigSettings(array $config = null)
    {
        $credentials = $config['auth_profiles']['service_account']['credentials_json'];

        $this->validateConfigSetting($credentials);
    }

    protected function validateOAuthConfigSettings(array $config = null)
    {
        $credentials = $config['auth_profiles']['oauth']['credentials_json'];

        $this->validateConfigSetting($credentials);

        $token = $config['auth_profiles']['oauth']['token_json'];

        $this->validateConfigSetting($token);
    }

    protected function validateOAuthConsentConfigSettings(array $config = null)
    {
        $credentials = $config['auth_profiles']['oauth']['credentials_json'];

        $this->validateConfigSetting($credentials);

        $token = $config['auth_profiles']['oauth']['token_json'];

        $this->validateConfigSetting($token);
    }



    protected function validateConfigSetting(string $setting)
    {
        if (! is_array($setting) && ! is_string($setting)) {
            throw InvalidConfiguration::credentialsTypeWrong($setting);
        }

        if (is_string($setting) && ! file_exists($setting)) {
            throw InvalidConfiguration::credentialsJsonDoesNotExist($setting);
        }
    }
}
