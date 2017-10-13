<?php

require_once __DIR__ . '/vendor/autoload.php';

use Google\Auth\Middleware\ScopedAccessTokenMiddleware;
use Google\Auth\OAuth2;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

/**
 * Make a request to an application protected by Identity-Aware Proxy.
 *
 * @param string $url The Identity-Aware Proxy-protected URL to fetch.
 * @param string $clientId The client ID used by Identity-Aware Proxy.
 *
 * @return The response body.
 */
function make_iap_request($url, $clientId, $pathToServiceAccount)
{
    $serviceAccountKey = json_decode(file_get_contents($pathToServiceAccount), true);
    $oauth_token_uri = 'https://www.googleapis.com/oauth2/v4/token';
    $iam_scope = 'https://www.googleapis.com/auth/iam';

    # Create an OAuth object using the service account key
    $oauth = new OAuth2([]);
    $oauth->setGrantType(OAuth2::JWT_URN);
    $oauth->setSigningKey($serviceAccountKey['private_key']);
    $oauth->setSigningAlgorithm('RS256');
    $oauth->setAudience($oauth_token_uri);
    $oauth->setAdditionalClaims([
        'target_audience' => $clientId,
    ]);
    $oauth->setTokenCredentialUri($oauth_token_uri);
    $oauth->setIssuer($serviceAccountKey['client_email']);

    # Obtain an OpenID Connect token, which is a JWT signed by Google.
    $guzzle = new Client();
    $httpHandler = \Google\Auth\HttpHandler\HttpHandlerFactory::build($guzzle);
    $token = $oauth->fetchAuthToken($httpHandler);
    $idToken = $oauth->getIdToken();

    # Construct a ScopedAccessTokenMiddleware with the ID token.
    $middleware = new ScopedAccessTokenMiddleware(
        function() use ($idToken) {
            return $idToken;
        },
        $iam_scope
    );

    $stack = HandlerStack::create();
    $stack->push($middleware);

    # Create an HTTP Client using Guzzle and pass in the credentials.
    $http_client = new Client([
        'handler' => $stack,
        'base_uri' => $url,
        'auth' => 'scoped',
        'verify' => false
    ]);

    # Make an authenticated HTTP Request
    $response = $http_client->request('GET', '/', []);
    return (string) $response->getBody();
}

# Please rewrite these values to yours
$res = make_iap_request(
    'https://myserver.example.com',
    '657424576728-3t5uiqg5ktqj5hqk3j45btq5uq98faos.apps.googleusercontent.com',
    './gcp-project-14a614b2955c.json'
);

var_dump($res);
