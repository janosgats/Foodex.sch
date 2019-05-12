<?php

namespace Eszkozok
{

    require_once __DIR__ . '/Eszk.php';

    class GlobalSettings
    {
        private static $settings = array();

        private static function FetchGlobalSettings()
        {
            try
            {

                $conn = Eszk::initMySqliObject();

                $stmt = $conn->prepare("SELECT * FROM `globalsettings`;");

                if ($stmt->execute())
                {
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc())
                    {
                        $settings[$row["nev"]] = $row["ertek"];
                    }
                }
                else
                {
                    throw new \Exception('$stmt->execute() is false');
                }

                $conn->close();
            }
            catch (\Exception $e)
            {
                Eszk::dieToErrorPage('8692: ' . $e->getMessage());
            }
        }

        public static function GetSetting($name)
        {
            if (!isset(self::$settings[$name]))
            {
                self::FetchGlobalSettings();
            }

            if (!isset(self::$settings[$name]))
            {//Ha a fetchelés után sincs ilyen, akkor az adatbázisban nincs ilyen bejegyzés => HIBA!
                throw new \Exception('67348: Error: Nincs ilyen nevű Global Setting! (' . $name . ')');
            }
            else
                return self::$settings[$name];
        }

        public static function SetSetting($name, $value)
        {
            try
            {
                $conn = Eszk::initMySqliObject();
                $stmt = $conn->prepare("INSERT INTO `globalsettings` (`nev`,`ertek`) VALUES (?,?) ON DUPLICATE KEY UPDATE `nev`=VALUES(`nev`)+VALUES(`ertek`);");


                $stmt->bind_param('ss', $name, $value);

                if ($stmt->execute())
                {
                    self::$settings[$name] = $value;
                }
                $conn->close();

            }
            catch (\Exception $e)
            {
                Eszk::dieToErrorPage('8694: error in ' . __FUNCTION__ .' : ' . $e->getMessage());
            }
        }

    }
}