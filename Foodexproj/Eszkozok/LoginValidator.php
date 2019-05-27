<?php

namespace Eszkozok;

require_once __DIR__ . '/Eszk.php';
require_once __DIR__ . '/entitas/Profil.php';


class LoginValidator
{
    static public function AdminJog_DiesToErrorrPage()
    {
        try
        {
            if (self::IsLoginValid('adminjog'))
                return true;
        }
        catch (\Exception $e)
        {
        }

        Eszk::dieToErrorPage('Nincs admin jogosultságod.');
        die('Nincs admin jogosultságod.');
    }

    static public function AdminJog_ThrowsException()
    {
        try
        {
            if (self::IsLoginValid('adminjog'))
                return true;
        }
        catch (\Exception $e)
        {
        }

        throw new \Exception('Nincs admin jogosultságod.');
    }

    static public function AdminJog_NOEXIT()
    {
        try
        {
            if (self::IsLoginValid('adminjog'))
                return true;
        }
        catch (\Exception $e)
        {
        }
        return false;
    }

    static public function MuszJelJog_DiesToErrorrPage()
    {
        try
        {
            if (self::IsLoginValid('muszjeljog'))
                return true;
        }
        catch (\Exception $e)
        {
        }

        Eszk::dieToErrorPage('Nincs jogosultságod műszakra való jelentkezéshez.');
        die('Nincs jogosultságod műszakra való jelentkezéshez.');
    }

    static public function MuszJelJog_ThrowsException()
    {
        try
        {
            if (self::IsLoginValid('muszjeljog'))
                return true;
        }
        catch (\Exception $e)
        {
        }
        throw new \Exception('Nincs jogosultságod műszakra való jelentkezéshez.');
    }

    static public function MuszJelJog_NOEXIT()
    {
        try
        {
            if (self::IsLoginValid('muszjeljog'))
                return true;
        }
        catch (\Exception $e)
        {
        }
        return false;
    }

    static public function AccountSignedIn()
    {
        try
        {
            if (self::IsLoginValid('account_signed_in'))
                return true;
        }
        catch (\Exception $e)
        {
        }

        Eszk::RedirectUnderRoot('');
        die('Nem vagy bejelentkezve.');
    }

    /**
     * DO NOT USE THIS unless you know what you do! This function does not force the script to exit. This only returns false, when the login is invalid.
     **/
    static public function AccountSignedIn_NOEXIT()
    {
        try
        {
            if (self::IsLoginValid('account_signed_in'))
                return true;
        }
        catch (\Exception $e)
        {
        }
        return false;
    }


    /**
     * "ENGINE" section...
     */

    private static $cached_AdminJog = null;
    private static $cached_MuszJelJog = null;
    private static $cached_SessionToken = null;

    static private function IsLoginValid($logintype)
    {
        $conn = new \mysqli();
        try
        {
            if (!GlobalServerInitParams::$RequireAuth)
            {
                $_SESSION['profilint_id'] = GlobalServerInitParams::$DefaultIntID;
                return true;
            }

            if (!isset($_SESSION['profilint_id']))
            {
                Eszk::RedirectUnderRoot('');
                die('Nem vagy bejelentkezve.');
            }

            if (!isset($_SESSION['session_token']))
            {
                Eszk::RedirectUnderRoot('');
                die('Nem vagy bejelentkezve.');
            }

            if (self::$cached_AdminJog == null || self::$cached_MuszJelJog == null || self::$cached_SessionToken == null)
            {

                $conn = Eszk::initMySqliObject();

                $stmt = $conn->prepare("SELECT * FROM fxaccok WHERE internal_id = ?");

                $intidbuff = $_SESSION['profilint_id'];
                $stmt->bind_param('s', $intidbuff);

                if (!$stmt->execute())
                    throw new \Exception();

                $result = $stmt->get_result();
                if ($result->num_rows != 1)
                    throw new \Exception();

                $row = $result->fetch_array();

                self::$cached_AdminJog = $row['adminjog'];
                self::$cached_MuszJelJog = $row['muszjeljog'];
                self::$cached_SessionToken = $row['session_token'];
            }

            if ($_SESSION['session_token'] == self::$cached_SessionToken)
            {
                switch ($logintype)
                {
                    case 'account_signed_in':
                        return true;
                        break;

                    case 'adminjog':
                        if (self::$cached_AdminJog == 1)
                            return true;
                        break;

                    case 'muszjeljog':
                        if (self::$cached_MuszJelJog == 1)
                            return true;
                        break;
                }

                return false;
            }
            else
                throw new \Exception();


        }
        catch (\Exception $e)
        {
            return false;
        }
        finally
        {
            try
            {
                $conn->close();
            }
            catch (\Exception $ex)
            {
            }
        }
        return false;
    }
}