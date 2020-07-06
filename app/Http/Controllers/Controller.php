<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_Drive;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName('process image');
        $client->setScopes(Google_Service_Drive::DRIVE);
        $client->setAuthConfig(storage_path('app/credentials.json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $tokenPath = storage_path('app/token.json');

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);

            if ($accessToken) {
                $client->setAccessToken($accessToken);
            }
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

                //Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }

            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }

            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
//            if(empty($tokenValue)== true){
//                DB::connection('sqlite')->table('token')->insert(['tokenV' => json_encode($client->getAccessToken())]);
//            }

        }
        return $client;
    }
}
