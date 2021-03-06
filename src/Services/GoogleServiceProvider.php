<?php
/*
Pupilsight, Flexible & Open School System
 */

namespace Pupilsight\Services;

use Google_Client;
use Google_Service_Calendar;
use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Google API Services
 *
 * @version v18
 * @since   v18
 */
class GoogleServiceProvider extends AbstractServiceProvider
{
    /**
     * The provides array is a way to let the container know that a service
     * is provided by this service provider. Every service that is registered
     * via this service provider must have an alias added to this array or
     * it will be ignored.
     *
     * @var array
     */
    protected $provides = [
        'Google_Client',
        'Google_Service_Calendar',
    ];

    /**
     * This is where the magic happens, within the method you can
     * access the container and register or retrieve anything
     * that you need to, but remember, every alias registered
     * within this method must be declared in the `$provides` array.
     */
    public function register()
    {
        $container = $this->getContainer();

        $container->share(Google_Client::class, function () {
            $session = $this->getContainer()->get('session');

            try {
                // Setup the Client
                $client = new Google_Client();
                $client->setApplicationName($session->get('googleClientName'));
                $client->setScopes(array('email', 'profile', 'https://www.googleapis.com/auth/calendar'));
                $client->setClientId($session->get('googleClientID'));
                $client->setClientSecret($session->get('googleClientSecret'));
                $client->setRedirectUri($session->get('googleRedirectUri'));
                $client->setDeveloperKey($session->get('googleDeveloperKey'));
                $client->setAccessType('offline');

                if (!$session->has('googleAPIAccessToken')) {
                    return $client;
                }

                $client->setAccessToken($session->get('googleAPIAccessToken'));
                
                if ($client->isAccessTokenExpired()) {
                    // Re-establish the Client and get a new token
                    if ($session->exists('googleAPIRefreshToken')) {
                        $client->refreshToken($session->get('googleAPIRefreshToken'));
                        $session->set('googleAPIAccessToken', $client->getAccessToken());
                    } else {
                        return null;
                    }
                }
            } catch (\InvalidArgumentException $e) {
                return null;
            } catch (\Google_Service_Exception $e) {
                return null;
            }

            return $client;
        });

        $container->share(Google_Service_Calendar::class, function () {
            $client = $this->getContainer()->get(Google_Client::class);

            return $client ? new Google_Service_Calendar($client) : null;
        });
    }
}
