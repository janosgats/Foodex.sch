<?php
namespace Eszkozok
{
    //echo 'include path: ' . get_include_path();

    use GuzzleHttp\Exception\ConnectException;
    use Profil\Profil;


//require_once '../vendor/autoload.php';
//include_once './AuthSchProvider.php';

    class Eszk
    {
        public static function initMySqliObject()
        {
            $servername = "gjani.sch.bme.hu:3306";
            $username = "fxtestuser";
            $password = "fxtest1234";
            $dbname = "fxtestdb";

            $conn = new \mysqli($servername, $username, $password, $dbname);

            $conn->set_charset("utf8");

            return $conn;
        }

        public static function GetBejelentkezettProfilAdat()
        {

            if (!isset($_SESSION['profilint_id']))
                self::RedirectUnderRoot('');

            $internal_id = $_SESSION['profilint_id'];


            $ProfilNev = "";
            $UjMuszakJog = 0;
            try
            {
                $conn = self::initMySqliObject();

                if (!$conn)
                    throw new \Exception('$conn is \'false\'');

                $stmt = $conn->prepare("SELECT * FROM fxaccok WHERE internal_id = ?");
                if (!$stmt)
                    throw new \Exception('$stmt is \'false\'');

                $stmt->bind_param('s', $internal_id);

                if ($stmt->execute())
                {
                    $result = $stmt->get_result();

                    if ($result->num_rows == 1)//Még nem regisztrált
                    {
                        $row = $result->fetch_assoc();

                        if (isset($row['nev']))
                            $ProfilNev = $row['nev'];
                        if (isset($row['ujmuszakjog']))
                            $UjMuszakJog = $row['ujmuszakjog'];

                    }
                    else
                    {
                        unset($_SESSION['profilint_id']);
                        throw new \Exception('$result->num_rows != 1');
                    }
                }
            }
            catch (\Exception $e)
            {
                self::dieToErrorPage('1220: ' . $e->getMessage());
            }

            $end = get_included_files();
            set_include_path(dirname(end($end)));
            include_once "../profil/Profil.php";
            return new Profil($ProfilNev, $UjMuszakJog);
        }

        public static function initNewAuthSchProvider()
        {
            $redirectUri = "https://feverkill.com/bme/foodex/login.php";
            $clientId = "***REMOVED***";
            $clientSecret = "***REMOVED***";

            if (strpos($_SERVER["HTTP_HOST"], 'localhost') !== false || strpos($_SERVER["HTTP_HOST"], 'gjani.sch.bme.hu') !== false)
            {
                $redirectUri = "http://gjani.sch.bme.hu/foodex/login.php";
                $clientId = "***REMOVED***";
                $clientSecret = "***REMOVED***";
            }
            else if (strpos($_SERVER["HTTP_HOST"], 'feverkill.com') !== false)//Contains()
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

        /**
         *
         */
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

                session_start();

                if (!isset($_SESSION["InvalidStateCounter"]))
                {
                    $_SESSION["InvalidStateCounter"] = 0;
                }

                $_SESSION["InvalidStateCounter"] += 1;

                if ($_SESSION["InvalidStateCounter"] > 4)
                {
                    unset($_SESSION["InvalidStateCounter"]);
                    self::dieToErrorPage('991: Invalid state');
                }
                else
                    self::RedirectUnderRoot('login.php');

            }
            else
            {
                try
                {
                    unset($_SESSION["InvalidStateCounter"]);


                    // Try to get an access token using the authorization code grant.
                    $accessToken = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);

                    $_SESSION["AuthSchAccessToken"] = serialize($accessToken);


                    // We have an access token, which we may use in authenticated
                    // requests against the service provider's API.
                    //echo 'Access Token: ' . $accessToken->getToken() . "<br>";
                    //echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
                    //echo 'Expires in: ' . $accessToken->getExpires() . "<br>";
                    //echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";

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
                            self::FxTagMuvelet($resp);

                        }
                        else
                        {

                            ?>
                            <h3 style="color: red">Nem vagy FoodEx tag!</h3>
                            <?php
                            self::RedirectUnderRoot('nemkortag.html');
                        }
                        // var_dump($resp);
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

                    self::dieToErrorPage('996: ' . $e->getMessage());

                }

            }
        }

        /**
         * @param $resresp AuthSCH resource response
         */
        static function FxTagMuvelet($resresp)
        {
            try
            {
                if (!isset($resresp['internal_id']))
                    throw new \Exception("internal_id is not set in $resresp");

                $internal_id = $resresp['internal_id'];

                if (isset($resresp["displayName"]))
                    $displayName = $resresp['displayName'];

                $conn = self::initMySqliObject();

                if (!$conn)
                    throw new \Exception('$conn is \'false\'');

                $stmt = $conn->prepare("SELECT * FROM fxaccok WHERE internal_id = ?");
                if (!$stmt)
                    throw new \Exception('$stmt is \'false\'');

                $stmt->bind_param('s', $internal_id);

                if ($stmt->execute())
                {
                    $result = $stmt->get_result();

                    if ($result->num_rows == 0)//Még nem regisztrált
                    {
                        $ujmuszakjog = 0;

                        if (isset($displayName))
                        {
                            $stmt = $conn->prepare("INSERT INTO `fxaccok` (`internal_id`, `nev`, `ujmuszakjog`) VALUES (?, ?, ?);");
                            $stmt->bind_param('ssi', $internal_id, $displayName, $ujmuszakjog);
                        }
                        else
                        {
                            $stmt = $conn->prepare("INSERT INTO `fxaccok` (`internal_id`, `ujmuszakjog`) VALUES (?, ?);");
                            $stmt->bind_param('si', $internal_id, $ujmuszakjog);
                        }


                        if ($stmt->execute())
                        {

                        }
                        else
                            throw new \Exception('');

                    }
                    else
                    {//Már regisztrált acc

                        if (isset($displayName))
                        {

                            $row = $result->fetch_assoc();
                            //  var_dump($row);
                            //   var_dump($row['nev']);
                            //  var_dump($displayName);
                            if ($row['nev'] !== $displayName)
                            {//Frissítjük a nevet az adatbázisban, mert a mostani AuthSCH-s eltér a régitől

                                // echo "NÉVELTÉRÉS!";

                                $stmt = $conn->prepare("UPDATE `fxaccok` SET `nev` = ? WHERE `fxaccok`.`internal_id` = ?");
                                $stmt->bind_param('ss', $displayName, $internal_id);


                                if ($stmt->execute())
                                {

                                }
                                else
                                    throw new \Exception('');


                            }

                        }
                    }
                }
                else
                {
                    throw new \Exception('$stmt->execute() returns false');
                }


                $_SESSION['profilint_id'] = $internal_id;

                ?>

                <script>
                    window.location.replace("<?php echo self::GetRootURL() . 'profil' ?>");
                </script>

                <?php

            }
            catch (\Exception $e)
            {
                try
                {
                    $conn->close();
                }
                catch (\Exception $e)
                {
                }

                self::dieToErrorPage("1009: " . $e->getMessage());
            }
            try
            {
                $conn->close();
            }
            catch (\Exception $e)
            {
            }
        }

        public static function dieToErrorPage($errcode)
        {
            self::RedirectUnderRoot('statuspages/error.php?code=' . urlencode($errcode));
        }

        public static function RedirectUnderRoot($relurl)
        {
            ob_clean();
            $rooturl = self::GetRootURL();
            $url = $rooturl . $relurl;

            try
            {
                $tort = explode('?', $relurl);
                $relurlcsakurl = $tort[0];

                $urlparamnelkul = $rooturl . $relurlcsakurl;


                $params = [];

                if (count($tort) > 1)
                    $params = explode('&', $tort[1]);

                $parampairs = [];
                for ($i = 0; $i < count($params); ++$i)
                {
                    $parampairs[$i] = explode('=', $params[$i]);
                }

            }
            catch (\Exception $e)
            {
                echo $e->getMessage();
            }


            header('Location: ' . $url);
            ?>
            <script>
                window.location.replace(<?php echo $url;?>);
            </script>
            <form id="formtosubmitabc9871215487" action="<?php echo $urlparamnelkul; ?>" style="display: none">
                <?php
                if (isset($parampairs))
                {
                    foreach ($parampairs as $pair)
                    {
                        if (isset($pair[0]) && isset($pair[1]))
                        {
                            ?>
                            <input type="input" name="<?php echo $pair[0]; ?>" value="<?php echo $pair[1]; ?>" hidden>
                            <?php
                        }
                    }
                }
                ?>
            </form>
            <script>
                function redirectfromsubmitter()
                {
                    document.getElementById("formtosubmitabc9871215487").submit();
                }
                window.onload = function ()
                {
                    setTimeout(redirectfromsubmitter, 1);
                    setTimeout(redirectfromsubmitter, 30);
                    setTimeout(redirectfromsubmitter, 200);
                    setTimeout(redirectfromsubmitter, 700);
                    setTimeout(redirectfromsubmitter, 2000);
                    setTimeout(redirectfromsubmitter, 5000);
                };
            </script>
            <?php
            die('Navigate to: <a href="' . $url . '">' . $url . '</a>!');
        }

        public static function GetRootURL()
        {
            $ret = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . "/";

            if (strpos($_SERVER["HTTP_HOST"], 'localhost') !== false || strpos($_SERVER["HTTP_HOST"], 'gjani.sch.bme.hu') !== false)
            {
                $ret .= "foodex/";
            }
            else if (strpos($_SERVER["HTTP_HOST"], 'feverkill.com') !== false)//Contains()
            {
                $ret .= "bme/foodex/";
            }

            return $ret;

        }
    }
}