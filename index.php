<?php

session_start();

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

use GuzzleHttp\Client;

$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => OAUTH_CLIENT_ID,
    'clientSecret'            => OAUTH_CLIENT_SECRET,
    'redirectUri'             => OAUTH_CLIENT_REDIRECT_URI,
    'scopes'                  => 'openid personal/selbst:read',
    'urlAuthorize'            => 'https://api.hiorg-server.de/oauth/v1/authorize',
    'urlAccessToken'          => 'https://api.hiorg-server.de/oauth/v1/token',
    'urlResourceOwnerDetails' => 'https://api.hiorg-server.de/oauth/v1/userinfo',
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    // (e.g. state).
    $authorizationUrl = $provider->getAuthorizationUrl();

    // Get the state generated for you and store it to the session.
    $_SESSION['oauth2state'] = $provider->getState();

    // Redirect the user to the authorization URL.
    header('Location: ' . $authorizationUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || empty($_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {
    if (isset($_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
    }

    exit('Invalid state');

} else {

    try {

        // Try to get an access token using the authorization code grant.
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // We have an access token, which we may use in authenticated
        // requests against the service provider's API.
        echo 'Access Token: ' . $accessToken->getToken() . "<br>";
        echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
        echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
        echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br><br>";

        // Using the access token, we may look up details about the
        // resource owner.
        $resourceOwner = $provider->getResourceOwner($accessToken);

        var_export($resourceOwner->toArray());

        echo '<br><br>';

        // The provider provides a way to get an authenticated API request for
        // the service, using the access token; it returns an object conforming
        // to Psr\Http\Message\RequestInterface.
        $request = $provider->getAuthenticatedRequest(
            'GET',
            'https://api.hiorg-server.de/core/v1/personal/selbst',
            $accessToken
        );

        $client = new Client();
        $response = $client->send($request);

        $jsonResponse = $response->getBody()->getContents();
    
        $responseArray = json_decode($jsonResponse, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            var_dump($responseArray);
        } else {
            echo 'Fehler beim Dekodieren des JSON-Responses: ' . json_last_error_msg();
        }

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        // Failed to get the access token or user details.
        exit($e->getMessage());
    }

}
