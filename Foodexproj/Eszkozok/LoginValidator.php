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

    static public function PontLatJog_DiesToErrorrPage()
    {
        try
        {
            if (self::IsLoginValid('pontlatjog'))
                return true;
        }
        catch (\Exception $e)
        {
        }

        Eszk::dieToErrorPage('Nincs jogosultságod mások pontszámának megtekintésére.');
        die('Nincs jogosultságod mások pontszámának megtekintésére.');
    }

    static public function PontLatJog_ThrowsException()
    {
        try
        {
            if (self::IsLoginValid('pontlatjog'))
                return true;
        }
        catch (\Exception $e)
        {
        }
        throw new \Exception('Nincs jogosultságod mások pontszámának megtekintésére.');
    }

    static public function PontLatJog_NOEXIT()
    {
        try
        {
            if (self::IsLoginValid('pontlatjog'))
                return true;
        }
        catch (\Exception $e)
        {
        }
        return false;
    }

    static public function FxTag_DiesToErrorrPage()
    {
        try
        {
            if (self::IsLoginValid('fxtag'))
                return true;
        }
        catch (\Exception $e)
        {
        }

        Eszk::dieToErrorPage('Nem vagy Foodex tag.');
        die('Nem vagy Foodex tag.');
    }

    static public function FxTag_ThrowsException()
    {
        try
        {
            if (self::IsLoginValid('fxtag'))
                return true;
        }
        catch (\Exception $e)
        {
        }
        throw new \Exception('Nem vagy Foodex tag.');
    }

    static public function FxTag_NOEXIT()
    {
        try
        {
            if (self::IsLoginValid('fxtag'))
                return true;
        }
        catch (\Exception $e)
        {
        }
        return false;
    }


    static public function Ertekelo_DiesToErrorrPage()
    {
        try
        {
            if (self::IsLoginValid('ertekelo'))
                return true;
        }
        catch (\Exception $e)
        {
        }

        Eszk::dieToErrorPage('Nincs jogod az értékeléshez.');
        die('Nincs jogod az értékeléshez.');
    }

    static public function Ertekelo_ThrowsException()
    {
        try
        {
            if (self::IsLoginValid('ertekelo'))
                return true;
        }
        catch (\Exception $e)
        {
        }
        throw new \Exception('Nincs jogod az értékeléshez.');
    }

    static public function Ertekelo_NOEXIT()
    {
        try
        {
            if (self::IsLoginValid('ertekelo'))
                return true;
        }
        catch (\Exception $e)
        {
        }
        return false;
    }

    static public function AccountSignedIn_RedirectsToRoot()
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

    static public function AccountSignedIn_ThrowsException()
    {
        try
        {
            if (self::IsLoginValid('account_signed_in'))
                return true;
        }
        catch (\Exception $e)
        {
        }
        throw new \Exception('Nem vagy bejelentkezve.');
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

    public static function GetErtekeloKorokIdk()
    {
        if (self::$cached_ErtekeloKorok == null)
        {
            $stmt = self::GetConn()->prepare("SELECT * FROM korertekelok WHERE ertekelo = ? ORDER BY korid ASC");

            $intidbuff = $_SESSION['profilint_id'];
            $stmt->bind_param('s', $intidbuff);


            if (!$stmt->execute())
                throw new \Exception('$stmt->execute() if false in GetErtekeloKorok() function!');

            $result = $stmt->get_result();

            self::$cached_ErtekeloKorok = [];
            while ($row = $result->fetch_assoc())
            {
                self::$cached_ErtekeloKorok[] = $row['korid'];
            }
        }
        return self::$cached_ErtekeloKorok;
    }

    private static function GetConn()
    {
        if (self::$conn == null)
            self::$conn = Eszk::initMySqliObject();

        return self::$conn;
    }


    private static $cached_AdminJog = null;
    private static $cached_MuszJelJog = null;
    private static $cached_PontLatJog = null;
    private static $cached_FxTag = null;
    private static $cached_SessionToken = null;

    private static $cached_ErtekeloKorok = null;

    private static $conn = null;

    static private function IsLoginValid($logintype)
    {
        try
        {

            if (!GlobalServerInitParams::$RequireAuth)
            {
                $_SESSION['profilint_id'] = GlobalServerInitParams::$DefaultIntID;
                return true;
            }

            if (!isset($_SESSION['profilint_id']) || $_SESSION['profilint_id'] == '')
                throw new \Exception();

            if (!isset($_SESSION['session_token']) || $_SESSION['session_token'] == '' || $_SESSION['session_token'] == 'kijelentkezve')
                throw new \Exception();

            if (self::$cached_AdminJog == null || self::$cached_MuszJelJog == null || self::$cached_SessionToken == null || self::$cached_PontLatJog == null || self::$cached_FxTag == null)
            {

                $stmt = self::GetConn()->prepare("SELECT * FROM fxaccok WHERE internal_id = ?");

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
                self::$cached_PontLatJog = $row['pontlatjog'];
                self::$cached_FxTag = $row['fxtag'];
                self::$cached_SessionToken = $row['session_token'];
            }

            if (self::$cached_SessionToken == null || self::$cached_SessionToken == 'kijelentkezve')
                throw new \Exception();

            if ($_SESSION['session_token'] == self::$cached_SessionToken)
            {
                switch ($logintype)
                {
                    case 'account_signed_in':
                        return true;
                        break;

                    case 'adminjog':
                        if (self::$cached_AdminJog == 1 && self::$cached_FxTag == 1)
                            return true;
                        break;

                    case 'muszjeljog':
                        if (self::$cached_MuszJelJog == 1 && self::$cached_FxTag == 1)
                            return true;
                        break;
                    case 'pontlatjog':
                        if (self::$cached_PontLatJog == 1)
                            return true;
                        break;
                    case 'fxtag':
                        if (self::$cached_FxTag == 1)
                            return true;
                        break;
                    case 'ertekelo':
                        if (is_array(self::GetErtekeloKorokIdk()) && count(self::GetErtekeloKorokIdk()) > 0)
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
    }
}