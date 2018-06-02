<?php


namespace Eszkozok;

    use League\OAuth2\Client\Provider\GenericProvider;

    class AuthSchProvider extends  GenericProvider
    {
        public function __construct(array $options = [], array $collaborators = [])
        {
            parent::__construct($options, $collaborators);
        }


        protected function getScopeSeparator()
        {
            return '+';
        }
        protected function getAuthorizationQuery(array $params)
        {
            //return $this->buildQueryString($params);

            $scope = $params["scope"];

            if($scope != null)
                unset($params["scope"]);

            $query = http_build_query($params, null, '&', \PHP_QUERY_RFC3986);

            if($scope != null)
                $query .= "&scope=" . $scope;

            return $query;
        }
    }

