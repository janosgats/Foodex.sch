<?php
set_include_path(getcwd());
require_once 'vendor/autoload.php';
require_once 'Eszkozok/Eszk.php';
require_once 'Eszkozok/AuthSchProvider.php';
session_start();


if (isset($_SESSION["AuthSchAccessToken"]))
{


    $accessToken = unserialize($_SESSION["AuthSchAccessToken"]);


// We have an access token, which we may use in authenticated
// requests against the service provider's API.
    echo 'Access Token: ' . $accessToken->getToken() . "<br>";
    echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
    echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
    echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";

// Using the access token, we may look up details about the
// resource owner.

    $response = \Eszkozok\AuthSchProvider::getResourceResponse($accessToken);
    echo "<h2>A neved: " . $response["displayName"] . "</h2>";

    var_dump($response);
}
else
{
    echo "'AuthSchAccessToken' session variable isn't set!";
    \Eszkozok\Eszk::doAuthSchLogin();
}