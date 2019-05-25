<?php
namespace Eszkozok
{

    //echo 'include path: ' . get_include_path();

    use GuzzleHttp\Exception\ConnectException;
    use PHPMailer\PHPMailer\Exception;
    use Profil\Profil;
    use Symfony\Component\Debug\ExceptionHandler;

    require_once __DIR__ . '/entitas/Muszak.php';
    require_once __DIR__ . '/entitas/Kompenz.php';

    require_once __DIR__ . '/../vendor/autoload.php';

    require_once __DIR__ . '/ini.php';
    require_once __DIR__ . '/../foodexpws.php';

    require_once __DIR__ . '/AuthSchProvider.php';

    require_once __DIR__ . '/MonologHelper.php';
    require_once __DIR__ . '/GlobalSettings.php';


    class Eszk
    {
        /**
         * Ha a bejelentkezés nem érvényes, kilépteti az embert
         */
        static public function ValidateLogin()
        {
            if (!GlobalServerInitParams::$RequireAuth)
            {
                $_SESSION['profilint_id'] = GlobalServerInitParams::$DefaultIntID;
                return;
            }

            try
            {
                if (self::IsLoginValid())
                    return;
            }
            catch (\Exception $e)
            {
                self::RedirectUnderRoot('');
            }

            self::RedirectUnderRoot('');
        }

        static public function IsLoginValid()
        {
            try
            {

                if (!isset($_SESSION['profilint_id']))
                    throw new \Exception();

                if (!isset($_SESSION['session_token']))
                    throw new \Exception();


                $conn = self::initMySqliObject();

                if (!$conn)
                    throw new \Exception();

                $stmt = $conn->prepare("SELECT * FROM fxaccok WHERE internal_id = ?");
                if (!$stmt)
                    throw new \Exception();


                $intidbuff = $_SESSION['profilint_id'];
                $stmt->bind_param('s', $intidbuff);

                if ($stmt->execute())
                {
                    $result = $stmt->get_result();
                    if ($result->num_rows == 1)
                    {
                        $row = $result->fetch_array();

                        if ($_SESSION['session_token'] == $row['session_token'])
                        {
                            $conn->close();
                            return true;
                        }
                        else
                            throw new \Exception();

                    }
                    else
                        throw new \Exception();
                }
                $conn->close();
            }
            catch (\Exception $e)
            {
                return false;
            }
            return false;
        }

        /**
         * @returns TRUE, ha a $muszid műszak keretlétszámába benne van az $int_id account
         */
        static public function BenneVanEAKeretben($muszid, $int_id)
        {
            $conn = self::initMySqliObject();

            $ki = self::BenneVanEAKeretbenWithConn($muszid, $int_id, $conn);

            try
            {
                $conn->close();
            }
            catch (\Exception $e)
            {
            }

            return $ki;
        }

        /**
         * @returns TRUE, ha a $muszid műszak keretlétszámába benne van az $int_id account
         */
        static public function BenneVanEAKeretbenWithConn($muszid, $int_id, $conn)
        {
            try
            {
                if (!$conn)
                    throw new \Exception('SQL hiba: $conn is \'false\'');


                $MuszakLetszam = self::GetTaroltMuszakAdatWithConn($muszid, true, $conn)->letszam;


                $stmt = $conn->prepare("SELECT * FROM `fxjelentk` WHERE `muszid` = ? AND `status` = 1 ORDER BY `ID` ASC;");
                if (!$stmt)
                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                $stmt->bind_param('i', $muszid);

                if ($stmt->execute())
                {
                    $resultKeret = $stmt->get_result();
                    if ($resultKeret->num_rows > 0)
                    {
                        for ($i = 0; ($rowKeret = $resultKeret->fetch_assoc()) && $i < $MuszakLetszam; ++$i)
                        {
                            if ($int_id == $rowKeret['jelentkezo'])
                                return true;

                        }
                    }
                }
                else
                    throw new \Exception('$stmt->execute() nem sikerült' . ' :' . $conn->error);

            }
            catch (\Exception $e)
            {
                ob_clean();
                self::dieToErrorPage('4015: ' . $e->getMessage());
            }

            return false;
        }

        /**
         * @return TRUE, ha a $szo maganhangzoval, vagy úgy ejtendő számmal kezdődik
         * @param $szo a tesztelendő karakterlánc
         */
        public static function startsWidthMaganhangzo($szo)
        {
            $betu = mb_strtolower(mb_substr($szo, 0, 1));

            if ($betu == 'a' ||
                $betu == 'á' ||
                $betu == 'e' ||
                $betu == 'é' ||
                $betu == 'i' ||
                $betu == 'í' ||
                $betu == 'o' ||
                $betu == 'ó' ||
                $betu == 'ö' ||
                $betu == 'ő' ||
                $betu == 'u' ||
                $betu == 'ú' ||
                $betu == 'ü' ||
                $betu == 'ű' ||
                $betu == '1' ||
                $betu == '5'
            )
                return true;
            else
                return false;
        }

        public static function isEmailValid($email)
        {
            // Remove all illegal characters from email
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);

            // Validate e-mail
            if (filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function executeAsyncShellCommand($command)
        {
            // If windows, else
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            {
                system($command . " > NUL");
            }
            else
            {
                shell_exec("/usr/bin/nohup " . $command . " >/dev/null 2>&1 &");
            }
        }

        public static function getNameOfDayOfWeek($nth_day, $teljesnev)
        {
            switch ($nth_day)
            {
                case 1:
                    if ($teljesnev)
                        return 'Hétfő';
                    else
                        return 'H';
                case 2:
                    if ($teljesnev)
                        return 'Kedd';
                    else
                        return 'K';
                case 3:
                    if ($teljesnev)
                        return 'Szerda';
                    else
                        return 'Sze';
                case 4:
                    if ($teljesnev)
                        return 'Csütörtök';
                    else
                        return 'Cs';
                case 5:
                    if ($teljesnev)
                        return 'Péntek';
                    else
                        return 'P';
                case 6:
                    if ($teljesnev)
                        return 'Szombat';
                    else
                        return 'Szo';
                case 7:
                    if ($teljesnev)
                        return 'Vasárnap';
                    else
                        return 'V';
            }
            return '';
        }


        public static function GetTaroltMuszakAdat($muszakid, $statpageerr)
        {
            $conn = self::initMySqliObject();
            $ki = self::GetTaroltMuszakAdatWithConn($muszakid, $statpageerr, $conn);

            try
            {
                $conn->close();
            }
            catch (\Exception $e)
            {
            }

            return $ki;
        }

        public static function GetTaroltMuszakAdatWithConn($muszakid, $statpageerr, $conn)
        {
            try
            {
                $ki = new Muszak();

                if (!$conn)
                    throw new \Exception('SQL hiba: $conn is \'false\'');

                $stmt = $conn->prepare("SELECT * FROM `fxmuszakok` WHERE `ID` = ?;");
                if (!$stmt)
                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                $stmt->bind_param('i', $muszakid);

                if ($stmt->execute())
                {
                    $result = $stmt->get_result();

                    if ($result->num_rows == 1)
                    {
                        $row = $result->fetch_assoc();


                        $ki->ID = $muszakid;
                        $ki->kiirta = $row['kiirta'];
                        $ki->musznev = $row['musznev'];
                        $ki->idokezd = $row['idokezd'];
                        $ki->idoveg = $row['idoveg'];
                        $ki->letszam = $row['letszam'];
                        $ki->pont = $row['pont'];
                        $ki->mospont = $row['mospont'];
                        $ki->megj = $row['megj'];

                        return $ki;
                    }
                    else
                    {
                        throw new \Exception('$result->num_rows != 1');
                    }
                }
                else
                {
                    throw new \Exception('$stmt->execute() is false');
                }
            }
            catch (\Exception $e)
            {
                if ($statpageerr)
                    self::dieToErrorPage('8591: ' . $e->getMessage());
            }
        }

        public static function GetTaroltKompenzAdat($kompid, $statpageerr)
        {
            $conn = self::initMySqliObject();
            $ki = self::GetTaroltKompenzAdatWithConn($kompid, $statpageerr, $conn);

            try
            {
                $conn->close();
            }
            catch (\Exception $e)
            {
            }

            return $ki;
        }

        public static function GetTaroltKompenzAdatWithConn($kompid, $statpageerr, $conn)
        {
            try
            {
                $ki = new Kompenz();

                if (!$conn)
                    throw new \Exception('SQL hiba: $conn is \'false\'');

                $stmt = $conn->prepare("SELECT * FROM `kompenz` WHERE `ID` = ?;");
                if (!$stmt)
                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                $stmt->bind_param('i', $kompid);

                if ($stmt->execute())
                {
                    $result = $stmt->get_result();

                    if ($result->num_rows == 1)
                    {
                        $row = $result->fetch_assoc();


                        $ki->ID = $kompid;
                        $ki->int_id = $row['internal_id'];
                        $ki->pont = $row['pont'];
                        $ki->megj = $row['megj'];

                        return $ki;
                    }
                    else
                    {
                        throw new \Exception('$result->num_rows != 1');
                    }
                }
                else
                {
                    throw new \Exception('$stmt->execute() is false');
                }
            }
            catch (\Exception $e)
            {
                if ($statpageerr)
                    self::dieToErrorPage('8692: ' . $e->getMessage());
            }
        }

        public static function getColumnAdatTombFromInternalIdTomb($internidTomb, $oszlopnev)
        {
            $conn = self::initMySqliObject();
            $ki = self::getColumnAdatTombFromInternalIdTombWithConn($internidTomb, $oszlopnev, $conn);
            try
            {
                $conn->close();
            }
            catch (\Exception $e)
            {
            }

            return $ki;
        }

        public static function getColumnAdatTombFromInternalIdTombWithConn($internidTomb, $oszlopnev, $conn)
        {
            try
            {
                $oszlopnev = $conn->escape_string($oszlopnev);//Mert oszlop nevet nem lehet bindelni

                $kimenet = array();


                if (!$conn)
                    throw new \Exception('SQL hiba: $conn is \'false\'');

                for ($index = 0; $index < count($internidTomb); ++$index)
                {


                    $stmt = $conn->prepare("SELECT $oszlopnev FROM `fxaccok` WHERE `internal_id` = ?;");
                    if (!$stmt)
                        throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                    $stmt->bind_param('s', $internidTomb[$index]);


                    if ($stmt->execute())
                    {
                        $result = $stmt->get_result();

                        if ($result->num_rows == 0)
                        {
                            $kimenet[$index] = 'N/A';
                        }
                        else if ($result->num_rows == 1)
                        {
                            $row = $result->fetch_assoc();

                            $kimenet[$index] = $row[$oszlopnev];
                        }
                        else
                        {
                            throw new \Exception('Tobb, mint egy acc ugyan azzal az internal_id-vel.');
                        }
                    }
                    else
                    {
                        throw new \Exception('Az SQL parancs végrehajtása nem sikerült.' . ' :' . $conn->error);
                    }
                }

                return $kimenet;
            }
            catch (\Exception $e)
            {
                self::dieToErrorPage('8531: ' . $e->getMessage());
            }
        }

        public static function getJelentkezokListaja($muszakid)
        {
            $conn = self::initMySqliObject();

            $ki = getJelentkezokListajaWithConn($muszakid, $conn);
            try
            {
                $conn->close();
            }
            catch (\Exception $e)
            {
            }

            return $ki;

        }

        public static function getJelentkezokListajaWithConn($muszakid, $conn)
        {
            try
            {
                $kimenet = array();


                if (!$conn)
                    throw new \Exception('SQL hiba: $conn is \'false\'');


                $stmt = $conn->prepare("SELECT `jelentkezo` FROM `fxjelentk` WHERE `muszid` = ? AND `status` = 1 ORDER BY `ID` ASC;");
                if (!$stmt)
                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                $stmt->bind_param('s', $muszakid);


                if ($stmt->execute())
                {
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0)
                    {
                        $index = 0;
                        while ($row = $result->fetch_assoc())
                        {
                            $kimenet[$index] = $row['jelentkezo'];
                            ++$index;
                        }
                    }
                }
                else
                {
                    throw new \Exception('Az SQL parancs végrehajtása nem sikerült.' . ' :' . $conn->error);
                }

                return $kimenet;

            }
            catch (\Exception $e)
            {
                self::dieToErrorPage('8512: ' . $e->getMessage());
            }
        }

        public static function initMySqliObject()
        {
            $username = "fxtestuser";
            $password = "fxtest1234";
            $dbname = "fxtestdb";

            $servername = "gjani.sch.bme.hu:3306";
            if (strpos($_SERVER["HTTP_HOST"], 'foodex.sch.bme.hu') !== false)
            {
                $username = \Eszkozok\FoodexPWs::$FoodexSchDBUser;
                $password = \Eszkozok\FoodexPWs::$FoodexSchDBPassword;
                $dbname = "wadon_foodex";
                $servername = "hal-9000.sch.bme.hu:3306";
            }
            else if (strpos($_SERVER["HTTP_HOST"], 'gjani.sch.bme.hu') !== false)
            {
                $servername = "gjani.sch.bme.hu:3306";
            }
            else if (strpos($_SERVER["HTTP_HOST"], 'gjani.ddns.net') !== false || strpos($_SERVER["HTTP_HOST"], 'localhost') !== false)
            {
                $servername = "gjani.ddns.net:3306";
                $servername = "localhost:3306";//Mert a ddns-es címmel elérve nagyon lassú
            }


            $conn = new \mysqli($servername, $username, $password, $dbname);

            $conn->set_charset("utf8");

            if ($conn->connect_errno)
            {
                throw new \Exception('3219: ' . $conn->connect_error);
            }

            if (!$conn)
                throw new \Exception('3218: ' . '$conn is false!');
            return $conn;
        }

        public static function Export_Database($tables = false, $backup_name = false)
        {
            $mysqli = self::initMySqliObject();

            $name = $mysqli->query('SELECT DATABASE()');
            if ($row = $name->fetch_row())
                $name = $row[0];
            else
                $name = 'fx_db_Export';


            $queryTables = $mysqli->query('SHOW TABLES');

            while ($row = $queryTables->fetch_row())
            {
                $target_tables[] = $row[0];
            }
            if ($tables !== false)
            {
                $target_tables = array_intersect($target_tables, $tables);
            }
            foreach ($target_tables as $table)
            {
                $result = $mysqli->query('SELECT * FROM ' . $table);
                $fields_amount = $result->field_count;
                $rows_num = $mysqli->affected_rows;
                $res = $mysqli->query('SHOW CREATE TABLE ' . $table);
                $TableMLine = $res->fetch_row();
                $content = (!isset($content) ? '' : $content) . "\n\n" . $TableMLine[1] . ";\n\n";

                for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter = 0)
                {
                    while ($row = $result->fetch_row())
                    { //when started (and every after 100 command cycle):
                        if ($st_counter % 100 == 0 || $st_counter == 0)
                        {
                            $content .= "\nINSERT INTO " . $table . " VALUES";
                        }
                        $content .= "\n(";
                        for ($j = 0; $j < $fields_amount; $j++)
                        {
                            $row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
                            if (isset($row[$j]))
                            {
                                $content .= '"' . $row[$j] . '"';
                            }
                            else
                            {
                                $content .= '""';
                            }
                            if ($j < ($fields_amount - 1))
                            {
                                $content .= ',';
                            }
                        }
                        $content .= ")";
                        //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                        if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num)
                        {
                            $content .= ";";
                        }
                        else
                        {
                            $content .= ",";
                        }
                        $st_counter = $st_counter + 1;
                    }
                }
                $content .= "\n\n\n";
            }

            date_default_timezone_set('Europe/Budapest');

            //$backup_name = $backup_name ? $backup_name : $name."___(".date('H-i-s')."_".date('d-m-Y').")__rand".rand(1,11111111).".sql";
            $backup_name = ($backup_name ? $backup_name : $name) . "__" . date('Y-m-d_H-i-s', time()) . ".sql";
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $backup_name . "\"");
            echo $content;
            exit;
        }

        public static function GetBejelentkezettProfilAdat()
        {

            if (!isset($_SESSION['profilint_id']))
                self::RedirectUnderRoot('');

            $internal_id = $_SESSION['profilint_id'];


            return self::GetTaroltProfilAdat($internal_id);
        }

        public static function GetTaroltProfilInfo($internal_id)
        {
            require_once __DIR__ . '/entitas/ProfilInfo.php';
            $ProfInf = new ProfilInfo();

            $conn = 0;
            try
            {
                $conn = self::initMySqliObject();

                if (!$conn)
                    throw new \Exception('$conn is \'false\'');

                $stmt = $conn->prepare("SELECT * FROM profilinfo WHERE int_id = ?");
                if (!$stmt)
                    throw new \Exception('$stmt is \'false\'');

                $stmt->bind_param('s', $internal_id);

                if ($stmt->execute())
                {
                    $result = $stmt->get_result();
                    if ($result->num_rows == 0)
                    {
                        //throw new \Exception('A felhasználó nem található!');
                    }
                    else if ($result->num_rows == 1)
                    {
                        $row = $result->fetch_assoc();

                        if (isset($row['kedv_vicc']))
                            $ProfInf->KedvencVicc = $row['kedv_vicc'];

                    }
                    else
                    {
                        throw new \Exception('$result->num_rows != 1');
                    }
                }
            }
            catch (\Exception $e)
            {
                self::dieToErrorPage('1420: ' . $e->getMessage());
            }
            finally
            {
                try
                {
                    $conn->close();
                }
                catch (\Exception $e)
                {
                }
            }

            return $ProfInf;
        }

        public static function GetTaroltProfilAdat($internal_id)
        {
            $ProfilNev = "";
            $AdminJog = 0;
            $email = "";


            $conn = 0;
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
                    if ($result->num_rows == 0)
                    {
                        throw new \Exception('A felhasználó nem található!');
                    }
                    else if ($result->num_rows == 1)
                    {
                        $row = $result->fetch_assoc();

                        if (isset($row['nev']))
                            $ProfilNev = $row['nev'];
                        if (isset($row['adminjog']))
                            $AdminJog = $row['adminjog'];
                        if (isset($row['email']))
                            $email = $row['email'];

                    }
                    else
                    {
                        throw new \Exception('$result->num_rows != 1');
                    }
                }
            }
            catch (\Exception $e)
            {
                self::dieToErrorPage('1220: ' . $e->getMessage());
            }
            finally
            {
                try
                {
                    $conn->close();
                }
                catch (\Exception $e)
                {
                }
            }

            require_once __DIR__ . '/entitas/Profil.php';
            return new Profil($internal_id, $ProfilNev, $AdminJog, $email);
        }

        public static function initNewAuthSchProvider()
        {
            $redirectUri = "https://feverkill.com/bme/foodex/login.php";
            $clientId = \Eszkozok\FoodexPWs::$AuthSCH_ClientID_Feverkill;
            $clientSecret = \Eszkozok\FoodexPWs::$AuthSCH_ClientSecret_Feverkill;

            if (strpos($_SERVER["HTTP_HOST"], 'foodex.sch.bme.hu') !== false)
            {
                $redirectUri = "https://foodex.sch.bme.hu/login.php";
                $clientId = \Eszkozok\FoodexPWs::$AuthSCH_ClientID_FoodexSCH;
                $clientSecret = \Eszkozok\FoodexPWs::$AuthSCH_ClientSecret_FoodexSCH;
            }
            else if (strpos($_SERVER["HTTP_HOST"], 'gjani.sch.bme.hu') !== false)
            {
                $redirectUri = "http://gjani.sch.bme.hu/foodex/login.php";
                $clientId = \Eszkozok\FoodexPWs::$AuthSCH_ClientID_GjaniSCH;
                $clientSecret = \Eszkozok\FoodexPWs::$AuthSCH_ClientSecret_GjaniSCH;
            }
            else if (strpos($_SERVER["HTTP_HOST"], 'localhost') !== false || strpos($_SERVER["HTTP_HOST"], 'gjani.ddns.net') !== false)//Contains()
            {
                $redirectUri = "http://gjani.ddns.net/foodex/login.php";
                $clientId = \Eszkozok\FoodexPWs::$AuthSCH_ClientID_GjaniDDNS;
                $clientSecret = \Eszkozok\FoodexPWs::$AuthSCH_ClientSecret_GjaniDDNS;
            }
            else if (strpos($_SERVER["HTTP_HOST"], 'feverkill.com') !== false)//Contains()
            {
                $redirectUri = "https://feverkill.com/bme/foodex/login.php";
                $clientId = \Eszkozok\FoodexPWs::$AuthSCH_ClientID_Feverkill;
                $clientSecret = \Eszkozok\FoodexPWs::$AuthSCH_ClientSecret_Feverkill;
            }


            return new \Eszkozok\AuthSchProvider([
                'clientId' => $clientId,    // The client ID assigned to you by the provider
                'clientSecret' => $clientSecret,   // The client password assigned to you by the provider
                'redirectUri' => $redirectUri,
                'urlAuthorize' => 'https://auth.sch.bme.hu/site/login',
                'urlAccessToken' => 'https://auth.sch.bme.hu/oauth2/token',
                'urlResourceOwnerDetails' => 'https://auth.sch.bme.hu/api/profile',
                'scopes' => ['displayName', 'eduPersonEntitlement', 'mail']
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

        // Function to get the client ip address
        public static function get_client_ip_address()
        {
            $ipaddress = '';
            if (isset($_SERVER['HTTP_CLIENT_IP']))
                $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            else if (isset($_SERVER['HTTP_X_FORWARDED']))
                $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
            else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
                $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
            else if (isset($_SERVER['HTTP_FORWARDED']))
                $ipaddress = $_SERVER['HTTP_FORWARDED'];
            else if (isset($_SERVER['REMOTE_ADDR']))
                $ipaddress = $_SERVER['REMOTE_ADDR'];
            else
                $ipaddress = 'UNKNOWN';

            return $ipaddress;
        }

        /**
         *
         */
        public static function doAuthSchLogin()
        {
            $logger = new \MonologHelper('Eszk::doAuthSchLogin()');

            set_include_path(getcwd());

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

                            $logger->notice('Login attempt failed: nem kortag, nemkortag.html', [$resp['internal_id'], (isset($resp['displayName'])) ? $resp['displayName'] : 'No DisplayName', self::get_client_ip_address()]);
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
                    throw new \Exception('internal_id is not set in $resresp');

                $internal_id = $resresp['internal_id'];

                $displayName = null;
                if (isset($resresp['displayName']))
                    $displayName = $resresp['displayName'];


                $email = null;
                if (isset($resresp['mail']))
                    $email = $resresp['mail'];


                $session_token = base64_encode(openssl_random_pseudo_bytes(64));


                $conn = self::initMySqliObject();

                if (!$conn)
                    throw new \Exception('SQL hiba: $conn is \'false\'');

                $stmt = $conn->prepare("SELECT * FROM fxaccok WHERE internal_id = ?");
                if (!$stmt)
                    throw new \Exception('SQL hiba: $stmt is \'false\'');

                $stmt->bind_param('s', $internal_id);

                if ($stmt->execute())
                {
                    $result = $stmt->get_result();

                    if ($result->num_rows == 0)
                    {//Még nem regisztrált, új acc
                        $adminjog = 0;

                        $stmt = $conn->prepare("INSERT INTO `fxaccok` (`internal_id`, `nev`, `adminjog`, `email`, `session_token`) VALUES (?, ?, ?, ?, ?);");
                        $stmt->bind_param('ssiss', $internal_id, $displayName, $adminjog, $email, $session_token);


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

                            if ($displayName != null && $displayName !== $row['nev'])
                            {//Frissítjük a nevet az adatbázisban, mert a mostani AuthSCH-s eltér a régitől

                                $stmt = $conn->prepare("UPDATE `fxaccok` SET `nev` = ? WHERE `fxaccok`.`internal_id` = ?");
                                $stmt->bind_param('ss', $displayName, $internal_id);


                                if ($stmt->execute())
                                {

                                }
                                else
                                    throw new \Exception('Hiba a displayName frissítése során');


                            }

                            if ($email != null && $email !== $row['email'])
                            {//Frissítjük az e-mail címet az adatbázisban, mert a mostani AuthSCH-s eltér a régitől

                                $stmt = $conn->prepare("UPDATE `fxaccok` SET `email` = ? WHERE `fxaccok`.`internal_id` = ?");
                                $stmt->bind_param('ss', $email, $internal_id);


                                if ($stmt->execute())
                                {

                                }
                                else
                                    throw new \Exception('Hiba az email frissítése során');


                            }

                        }
                    }

                    //Session_token frissítése
                    $stmt = $conn->prepare("UPDATE `fxaccok` SET `session_token` = ? WHERE `fxaccok`.`internal_id` = ?");
                    $stmt->bind_param('ss', $session_token, $internal_id);

                    if ($stmt->execute())
                    {

                    }
                    else
                        throw new \Exception('Hiba a session_token frissítése során');
                }
                else
                {
                    throw new \Exception('$stmt->execute() returns false');
                }


                $_SESSION['profilint_id'] = $internal_id;
                $_SESSION['session_token'] = $session_token;

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
            try
            {
                $logger = new \MonologHelper('Eszk::dieToErrorPage()');
                $logger->error('$errcode: ' . $errcode, [(isset($_SESSION['profilint_id'])) ? $_SESSION['profilint_id'] : 'No Internal ID', self::get_client_ip_address()]);
            }
            catch (\Exception $e)
            {
            }
            self::RedirectUnderRoot('statuspages/error.php?code=' . urlencode($errcode));
        }

        public static function RedirectUnderRoot($relurl)
        {
            try
            {
                if (ob_get_length())
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

                if (ob_get_length())
                    ob_clean();

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
                                <input type="input" name="<?php echo $pair[0]; ?>" value="<?php echo $pair[1]; ?>"
                                       hidden>
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
            }
            catch (\Exception $e)
            {
            }
            die('Navigate to: <a href="' . $url . '">' . $url . '</a>!');
        }

        public static function GetRootURL()
        {
            $ret = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . "/";

            if (strpos($_SERVER["HTTP_HOST"], 'foodex.sch.bme') !== false)
            {
                $ret .= "";
            }
            else if (strpos($_SERVER["HTTP_HOST"], 'localhost') !== false || strpos($_SERVER["HTTP_HOST"], 'gjani.sch.bme.hu') !== false || strpos($_SERVER["HTTP_HOST"], 'gjani.ddns.net') !== false)
            {
                $ret .= "foodex/";
            }
            else if (strpos($_SERVER["HTTP_HOST"], 'feverkill.com') !== false)//Contains()
            {
                $ret .= "bme/foodex/";
            }

            return $ret;

        }


        public static function GetAccPontok($int_id)
        {
            $conn = self::initMySqliObject();
            $ki = self::GetAccPontokWithConn($int_id, $conn);

            try
            {
                $conn->close();
            }
            catch (\Exception $e)
            {
            }

            return $ki;
        }

        public static function GetAccPontokWithConn($int_id, $conn)
        {
            try
            {

                $MuszakLetszamok = array();//Cacheli az muszid - Létszám párokat a műszakok közül, hogy ne kelljen minden műszaknál új lekérdezés a létszámért

                $pontszam = 0;

                $stmt = $conn->prepare("SELECT `muszid`, `mosogat` FROM `fxjelentk` WHERE `jelentkezo` = ? AND `status` = 1;");
                if (!$stmt)
                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                $stmt->bind_param('s', $int_id);

                if ($stmt->execute())
                {
                    $resultJelentk = $stmt->get_result();
                    if ($resultJelentk->num_rows > 0)
                    {
                        $jelMuszakIDk = array();//Jelentkezett műszakok ID-i a $int_id-hoz
                        $jelMosogatasok = array();

                        while ($rowJelentk = $resultJelentk->fetch_assoc())
                        {
                            $aktmuszidBuff = $conn->escape_string($rowJelentk['muszid']);
                            $jelMuszakIDk[] = $aktmuszidBuff;
                            $jelMosogatasok[$aktmuszidBuff] = $conn->escape_string($rowJelentk['mosogat']);
                        }

                        // var_dump($jelMuszakIDk);

                        $vittMuszakIDk = array();
                        $vittMosogatasok = array();

                        foreach ($jelMuszakIDk as $muszidakt)
                        {
                            if (!array_key_exists($muszidakt, $MuszakLetszamok))
                            {
                                $buff = self::GetTaroltMuszakAdatWithConn($muszidakt, false, $conn);
                                if ($buff != false)
                                    $MuszakLetszamok[$muszidakt] = $buff->letszam;
                            }


                            $stmt = $conn->prepare("SELECT * FROM `fxjelentk` WHERE `muszid` = ? AND `status` = 1 ORDER BY `ID` ASC;");
                            if (!$stmt)
                                throw new \Exception('SQL hiba: $stmt 5 is \'false\'' . ' :' . $conn->error);

                            $stmt->bind_param('i', $muszidakt);

                            if ($stmt->execute())
                            {
                                $resultKeret = $stmt->get_result();
                                if ($resultKeret->num_rows > 0)
                                {

                                    for ($i = 0; ($rowKeret = $resultKeret->fetch_assoc()) && isset($MuszakLetszamok[$muszidakt]) && $i < $MuszakLetszamok[$muszidakt]; ++$i)
                                    {
                                        //echo $i . ' - ' . $muszidakt . '<br>';


                                        if ($int_id == $rowKeret['jelentkezo'])
                                        {
                                            $vittMuszakIDk[] = $muszidakt;
                                            if ($jelMosogatasok[$muszidakt] == 1)//Ha az aktuálisan vitt műszakban mosogatott
                                                $vittMosogatasok[] = $muszidakt;
                                            break;
                                        }

                                        // var_dump($rowKeret);

                                    }
                                }
                            }
                            else
                                throw new \Exception('$stmt->execute() 5 nem sikerült' . ' :' . $conn->error);
                        }

                        if (count($vittMuszakIDk) > 0)
                        {
                            //`idoveg` < NOW() : Csak arra a műszakra kap pontot, ami már lezárult


                            $stmt = $conn->prepare("SELECT SUM(`pont`) AS OsszPontszam FROM `fxmuszakok` WHERE (FALSE || `idoveg` < NOW()) AND ( `idokezd` BETWEEN '" . \Eszkozok\GlobalSettings::GetSetting('pontozasi_idoszak_kezdete') . "' AND '" . \Eszkozok\GlobalSettings::GetSetting('pontozasi_idoszak_vege') . "' ) AND `ID` IN (" . implode(',', $vittMuszakIDk) . ");");
                            if (!$stmt)
                                throw new \Exception('SQL hiba: $stmt 3 is \'false\'' . ' :' . $conn->error);

                            if ($stmt->execute())
                            {
                                $resultMuszak = $stmt->get_result();
                                if ($resultMuszak->num_rows == 1)
                                {
                                    $rowMuszak = $resultMuszak->fetch_assoc();
                                    $pontszam += $rowMuszak['OsszPontszam'];
                                }
                                if (count($vittMosogatasok) > 0)
                                {
                                    $stmt = $conn->prepare("SELECT SUM(`mospont`) AS OsszPontszam FROM `fxmuszakok` WHERE (FALSE || `idoveg` < NOW()) AND ( `idokezd` BETWEEN '" . \Eszkozok\GlobalSettings::GetSetting('pontozasi_idoszak_kezdete') . "' AND '" . \Eszkozok\GlobalSettings::GetSetting('pontozasi_idoszak_vege') . "' ) AND `ID` IN (" . implode(',', $vittMosogatasok) . ");");
                                    if (!$stmt)
                                        throw new \Exception('SQL hiba: $stmt 4 is \'false\'' . ' :' . $conn->error);

                                    if ($stmt->execute())
                                    {
                                        $resultMuszak = $stmt->get_result();
                                        if ($resultMuszak->num_rows == 1)
                                        {
                                            $rowMuszak = $resultMuszak->fetch_assoc();
                                            $pontszam += $rowMuszak['OsszPontszam'];
                                        }
                                    }
                                    else
                                        throw new \Exception('$stmt->execute() 4 nem sikerült' . ' :' . $conn->error);
                                }
                            }
                            else
                                throw new \Exception('$stmt->execute() 3 nem sikerült' . ' :' . $conn->error);
                        }
                    }
                }
                else
                    throw new \Exception('$stmt->execute() 2 nem sikerült' . ' :' . $conn->error);

                return round($pontszam + self::GetAccKompenzaltPontokWithConn($int_id, $conn), 1);


            }
            catch (\Exception $e)
            {
                throw $e;
                //ob_clean();
                //Eszkozok\Eszk::dieToErrorPage('3014: ' . $e->getMessage());
            }
        }

        public static function GetAccKompenzaltPontokWithConn($int_id, $conn)
        {

            $stmt = $conn->prepare("SELECT `pont` FROM `kompenz` WHERE ( `ido` BETWEEN '" . \Eszkozok\GlobalSettings::GetSetting('pontozasi_idoszak_kezdete') . "' AND '" . \Eszkozok\GlobalSettings::GetSetting('pontozasi_idoszak_vege') . "' ) AND `internal_id` = ?;");
            if (!$stmt)
                throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

            $buffInt = $int_id;
            $stmt->bind_param('s', $buffInt);

            if ($stmt->execute())
            {
                $kipont = 0;

                $resultKomp = $stmt->get_result();
                while ($rowKomp = $resultKomp->fetch_assoc())
                {
                    $kipont += $rowKomp['pont'];
                }

                return $kipont;
            }
            else
                throw new \Exception('$stmt->execute() 2 nem sikerült' . ' :' . $conn->error);
        }

//        public static function GetGlobalSettings(array $options)
//        {
//            $conn = self::initMySqliObject();
//            $ki = self::GetGlobalSettingsWithConn($options, $conn);
//
//            try
//            {
//                $conn->close();
//            }
//            catch (\Exception $e)
//            {
//            }
//
//            return $ki;
//        }
//
//        public static function GetGlobalSettingsWithConn(array $options, \mysqli $conn)
//        {
//            try
//            {
//                $ki = [];
//
//                if (!$conn)
//                    throw new \Exception('SQL hiba: $conn is \'false\'');
//
//
//                $EscapedOptions = [];
//
//                foreach ($options as $opt)
//                {
//                    array_push($EscapedOptions, "'" . $conn->real_escape_string($opt) . "'");
//                }
//
//
//                $stmt = $conn->prepare("SELECT * FROM `globalsettings` WHERE `nev` IN (" . implode(',', $EscapedOptions) . ");");
//
//                if (!$stmt)
//                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);
//
//                if ($stmt->execute())
//                {
//                    $result = $stmt->get_result();
//
//                    while ($row = $result->fetch_assoc())
//                    {
//                        $ki[$row["nev"]] = $row["ertek"];
//                        $GLOBALS[$row["nev"]] = $row["ertek"];
//                    }
//                }
//                else
//                {
//                    throw new \Exception('$stmt->execute() is false');
//                }
//
//                return $ki;
//            }
//            catch (\Exception $e)
//            {
//                self::dieToErrorPage('8692: ' . $e->getMessage());
//            }
//        }
//
//        public static function SetGlobalSettings($optionNev, $ertek)
//        {
//            $conn = self::initMySqliObject();
//            $ki = self::SetGlobalSettingsWithConn($optionNev, $ertek, $conn);
//
//            try
//            {
//                $conn->close();
//            }
//            catch (\Exception $e)
//            {
//            }
//
//            return $ki;
//        }
//
//        public static function SetGlobalSettingsWithConn($optionNev, $ertek, \mysqli $conn)
//        {
//            try
//            {
//                if (!$conn)
//                    throw new \Exception('SQL hiba: $conn is \'false\'');
//
//                $stmt = $conn->prepare("UPDATE `globalsettings` SET `ertek`=? WHERE `nev`=?");
//
//                $stmt->bind_param('ss', $ertek, $optionNev);
//
//                if ($stmt->execute())
//                {
//                    $GLOBALS[$optionNev] = $ertek;
//                    return;
//
////                    if($stmt->affected_rows == 1)
////                        return;
////                    else
////                        throw new \Exception('$stmt->affected_rows != 1. (It is '. $stmt->affected_rows . '.');
//                }
//                else
//                {
//                    throw new \Exception('$stmt->execute() is false');
//                }
//
//                throw new \Exception('FUNCTION END unexpectedly REACHED');
//            }
//            catch (\Exception $e)
//            {
//                self::dieToErrorPage('8692: ' . $e->getMessage());
//            }
//        }

        public static function IsDatestringInPontozasiIdoszak($datebe)
        {

            return \Eszkozok\GlobalSettings::GetSetting('pontozasi_idoszak_kezdete') <= $datebe && $datebe <= \Eszkozok\GlobalSettings::GetSetting('pontozasi_idoszak_vege');
        }


        public static function GetJelDelayTimeByPont($pontszam)
        {
            $conn = self::initMySqliObject();
            $ki = self::GetJelDelayTimeByPontWithConn($pontszam, $conn);

            try
            {
                $conn->close();
            }
            catch (\Exception $e)
            {
            }

            return $ki;
        }

        public static function GetJelDelayTimeByPontWithConn($pontszam, \mysqli $conn)
        {
            $stmt = $conn->prepare("SELECT delay FROM pontjeldelay WHERE minpont <= ? ORDER BY minpont DESC LIMIT 1;");
            $stmt->bind_param("d", $pontszam);

            if ($stmt->execute())
            {
                $result = $stmt->get_result();
                if ($result->num_rows == 0)
                    return 0;//no delay time
                else
                {
                    return $result->fetch_assoc()['delay'];
                }
            }
            else
                throw new \Exception('34525: Cannot calculate delay time for score. $stmt->execute() is false');
        }

        public static function GetMuszakActivationTimeByMuszidWithConn($muszid, \mysqli $conn)
        {
            $stmt = $conn->prepare("SELECT `datetime` FROM `logs` WHERE `message` = 'MUSZAKTIVAL' AND `context` = CONCAT('[',  ?, ']' );");
            $stmt->bind_param("i", $muszid);

            if ($stmt->execute())
            {
                $result = $stmt->get_result();
                if ($result->num_rows == 1)
                {
                    return $result->fetch_assoc()['datetime'];
                }
            }

            throw new \Exception('34525: Hiba  a műszak aktiválási idejének megállapításakor. $stmt->execute() is false');
        }

    }
}