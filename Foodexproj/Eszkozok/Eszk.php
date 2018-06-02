<?php

namespace Eszkozok;

set_include_path(getcwd());
//require_once '../vendor/autoload.php';
//include_once './AuthSchProvider.php';

class Eszk
{
    public static function initNewAuthSchProvider()
    {
        $redirectUri = "https://feverkill.com/bme/foodex/login.php";
        $clientId = "***REMOVED***";
        $clientSecret = "***REMOVED***";

        if ($_SERVER["HTTP_HOST"] == 'localhost')
        {
            $redirectUri = "http://gjani.sch.bme.hu/foodex/login.php";
            $clientId = "***REMOVED***";
            $clientSecret = "***REMOVED***";
        }
        else if ($_SERVER["HTTP_HOST"] == 'feverkill.com')
        {
            $redirectUri = "https://feverkill.com/bme/foodex/login.php";
            $clientId = "***REMOVED***";
            $clientSecret = "***REMOVED***";
        }


        return new \Eszkozok\AuthSchProvider([
            'clientId' => $clientId,    // The client ID assigned to you by the provider
            'clientSecret' => $clientSecret,   // The client password assigned to you by the provider
            'redirectUri' => $redirectUri,
            'urlAuthorize' => 'https://auth.sch.bme.hu/site/login',
            'urlAccessToken' => 'https://auth.sch.bme.hu/oauth2/token',
            'urlResourceOwnerDetails' => 'https://auth.sch.bme.hu/api/profile',
            'scopes' => ['displayName', 'eduPersonEntitlement']
        ]);
    }

    /**
     * @param $kortagsagok A resource response-ból a körtagságok tömb rész.
     * @return Ha körtag: a Fx körtagság információit tartalmazó tömb. Ha nem körtag: akkor 'false'
     */
    public static function testFoodexKortagsag($kortagsagok)
    {
        foreach ($kortagsagok as $kor)
        {
            try
            {
                if ($kor['name'] == 'FoodEx')
                    return $kor;
            }
            catch (Exception $e)
            {
            }
        }

        return false;
    }

    public static function doAuthSchLogin()
    {
        set_include_path(getcwd());
        require_once 'vendor/autoload.php';
        include_once 'Eszkozok/AuthSchProvider.php';

        $provider = self::initNewAuthSchProvider();


        // If we don't have an authorization code then get one
        if (!isset($_GET['code']))
        {

            // Fetch the authorization URL from the provider; this returns the
            // urlAuthorize option and generates and applies any necessary parameters
            // (e.g. state).
            $authorizationUrl = $provider->getAuthorizationUrl();

            // Get the state generated for you and store it to the session.
            $_SESSION['oauth2state'] = $provider->getState();

            // Redirect the user to the authorization URL.
            header('Location: ' . $authorizationUrl);
            ?>
            <script>
                window.location.replace("<?php echo $authorizationUrl; ?>");
            </script>
            <?php
            exit;

            // Check given state against previously stored one to mitigate CSRF attack
        }
        elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state']))
        {

            if (isset($_SESSION['oauth2state']))
            {
                unset($_SESSION['oauth2state']);
            }

            exit('Invalid state');

        }
        else
        {

            try
            {


                // Try to get an access token using the authorization code grant.
                $accessToken = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);

                $_SESSION["AuthSchAccessToken"] = serialize($accessToken);


                // We have an access token, which we may use in authenticated
                // requests against the service provider's API.
                echo 'Access Token: ' . $accessToken->getToken() . "<br>";
                echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
                echo 'Expires in: ' . $accessToken->getExpires() . "<br>";
                echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";

                // Using the access token, we may look up details about the
                // resource owner.

                if ($accessToken->hasExpired() != 'expired')
                {
                    $resp = \Eszkozok\AuthSchProvider::getResourceResponse($accessToken);

                    $kortagsagok = $resp['eduPersonEntitlement'];

                    if ((($tagsag = self::testFoodexKortagsag($kortagsagok)) != false))
                    {
                        ?>
                        <h3 style="color: green">FoodEx <?php echo $tagsag['status']; ?> vagy!</h3>
                        <?php
                    }
                    else
                    {

                        ?>
                        <h3 style="color: red">Nem vagy FoodEx tag!</h3>
                        <?php
                        ob_clean();
                        include 'nemkortag.html';
                    }
                    var_dump($resp);
                }

//        $resourceOwner = $provider->getResourceOwner($accessToken);
//
//        var_export($resourceOwner->toArray());
//        echo "<br><br><br><br><br><br><br>";
//        // The provider provides a way to get an authenticated API request for
//        // the service, using the access token; it returns an object conforming
//        // to Psr\Http\Message\RequestInterface.
//        $request = $provider->getAuthenticatedRequest(
//            'GET',
//            'https://auth.sch.bme.hu/api/profile',
//            $accessToken
//        );
//
//        $client = new \GuzzleHttp\Client();
//        $response = $client->send($request);
//        var_dump($response);

            }
            catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e)
            {

                // Failed to get the access token or user details.
                exit($e->getMessage());

            }

        }
    }
}