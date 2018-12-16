<?php


namespace Eszkozok;

include_once __DIR__ .'/Eszk.php';

use League\OAuth2\Client\Provider\GenericProvider;

class AuthSchProvider extends GenericProvider
{
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
    }


    public static function getResourceResponse($accessToken)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://auth.sch.bme.hu/api/profile/?access_token=" . $accessToken->getToken());
        // Set so curl_exec returns the result instead of outputting it.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Get the response and close the channel.
        $response = curl_exec($ch);
        curl_close($ch);

     //   echo "X1-";

        $response = json_decode($response, true);

//        echo "X2-";
//        var_dump($response);
//        echo "X3-";
//        var_dump($response["displayName"]);
//        echo "X4-";

        $errbe = null;
        if (isset($response['error']))
        {
            $errbe = $response['error'];
        }
        if ($accessToken->hasExpired() == 'expired' || ($errbe != null && $errbe == "invalid_token"))
        {
            Eszk::doAuthSchLogin();
        }

       // echo "X9-";
        return $response;
    }


    protected function getScopeSeparator()
    {
        return '+';
    }

    protected function getAuthorizationQuery(array $params)
    {
        //return $this->buildQueryString($params);

        $scope = $params["scope"];

        if ($scope != null)
            unset($params["scope"]);

        $query = http_build_query($params, null, '&', \PHP_QUERY_RFC3986);

        if ($scope != null)
            $query .= "&scope=" . $scope;

        return $query;
    }
}

