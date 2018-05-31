<?php

namespace Eszkozok
{

    class Includer
    {
        public static function include_oauth2_client()
        {
//            include '3rdparty/oauth2-client/';
            include '3rdparty/oauth2-client/Tool/RequiredParameterTrait.php';
            include '3rdparty/oauth2-client/Grant/AbstractGrant.php';
            include '3rdparty/oauth2-client/Grant/AuthorizationCode.php';
            include '3rdparty/oauth2-client/Grant/Exception/InvalidGrantException.php';
            include '3rdparty/oauth2-client/Tool/RequestFactory.php';
            include '3rdparty/oauth2-client/Grant/GrantFactory.php';
            include '3rdparty/oauth2-client/Tool/BearerAuthorizationTrait.php';
            include '3rdparty/oauth2-client/Tool/QueryBuilderTrait.php';
            include '3rdparty/oauth2-client/Tool/ArrayAccessorTrait.php';
            include '3rdparty/oauth2-client/Provider/AbstractProvider.php';
            include '3rdparty/oauth2-client/Provider/GenericProvider.php';
        }


        /**
         * Minden .php fájlt include_once-ol, ami rekurzívan ez alatt a mappa alatt van.
         * @param $dir Az includolás gyökere.
         */
        public static function includeallfromdir($dir)
        {
            $eredetiincludepath = get_include_path();
            set_include_path($dir);


            $entries = scandir($dir);

            foreach ($entries as $entry)
            {
                $fullpath = $dir . "/" . $entry;

                if (is_file($fullpath))
                {
                    if (preg_match("/^.*\\.(php)$/", $entry))
                    {
                        //echo "FILE: " . $fullpath . '<br>';
                        include_once $fullpath;
                    }
                }
                else if (is_dir($fullpath))
                {
                    if ($entry != "." && $entry != "..")
                    {
                        //echo "DIR: &nbsp;" . $fullpath . '<br>';
                        self::includeallfromdir($fullpath);
                    }
                }
            }

            set_include_path($eredetiincludepath);
        }
    }
}