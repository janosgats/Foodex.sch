<?php
ob_start();
session_start();

set_include_path(getcwd());
require_once '../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once '../Eszkozok/MonologHelper.php';
require_once '../Eszkozok/entitas/Muszak.php';


require_once __DIR__ . '/../Eszkozok/param.php';


function verifyDate($date, $strict = true)
{
    $dateTime = DateTime::createFromFormat('Y-m-d G:i', $date);
    if ($strict)
    {
        $errors = DateTime::getLastErrors();
        if (!empty($errors['warning_count']))
        {
            return false;
        }
    }

    $dateTime2 = DateTime::createFromFormat('Y-m-d G:i:s', $date);
    if ($strict)
    {
        $errors = DateTime::getLastErrors();
        if (!empty($errors['warning_count']))
        {
            return false;
        }
    }
    return $dateTime !== false || $dateTime2 !== false;
}

try
{
    \Eszkozok\LoginValidator::AdminJog_DiesToErrorrPage();

    $logger;
    try
    {
        $logger = new \MonologHelper('muszedit/edit.php');
    }
    catch (\Exception $e)
    {
    }

    $internal_id = $_SESSION['profilint_id'];

    $MuszakID;

    $conn;
    $stmt;

    $MuszakTorlesE = (IsURLParamSet('musztorles') && GetURLParam('musztorles') == 1);
    $MuszakID = 'URL PARAM IS NOT SET';
    if (IsURLParamSet('muszid'))
        $MuszakID = GetURLParam('muszid');

    if ($MuszakTorlesE)
    {//Torles//

        if (!is_numeric($MuszakID))
            throw new \Exception('A műszak ID-je nem megfelelő: ' . htmlspecialchars($MuszakID));

        $conn = Eszkozok\Eszk::initMySqliObject();


        if (!$conn)
            throw new \Exception('SQL hiba: $conn is \'false\'');

        $stmt = $conn->prepare("DELETE FROM `fxmuszakok` WHERE `fxmuszakok`.`ID` = ?;");

        if (!$stmt)
            throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

        $stmt->bind_param('i', $MuszakID);

    }
    else
    {//Szerkesztes//
        $AktMuszak = new \Eszkozok\Muszak();
        $AktMuszak->kiirta = $internal_id;


            $AktMuszak->ID = $MuszakID;

        if (IsURLParamSet('musznev'))
            $AktMuszak->musznev = GetURLParam('musznev');
        if (IsURLParamSet('letszam'))
            $AktMuszak->letszam = GetURLParam('letszam');
        if (IsURLParamSet('pont'))
            $AktMuszak->pont = GetURLParam('pont');
        if (IsURLParamSet('mospont'))
            $AktMuszak->mospont = GetURLParam('mospont');
        if (IsURLParamSet('idokezd'))
            $AktMuszak->idokezd = GetURLParam('idokezd');
        if (IsURLParamSet('idoveg'))
            $AktMuszak->idoveg = GetURLParam('idoveg');
        if (IsURLParamSet('korid'))
            $AktMuszak->korID = GetURLParam('korid');
        if (IsURLParamSet('megj'))
            $AktMuszak->megj = GetURLParam('megj');

        if (!is_numeric($AktMuszak->pont))
            throw new \Exception('A közösségi pontszám nem egy szám.');
        if (!is_numeric($AktMuszak->mospont))
            throw new \Exception('A mosogatásért járó pontszám nem egy szám.');
        if (!is_numeric($AktMuszak->letszam))
            throw new \Exception('A létszám nem egy szám.');
        if (!is_numeric($AktMuszak->ID))
            throw new \Exception('Az ID nem egy szám.');


        if (!(is_numeric($AktMuszak->korID) || $AktMuszak->korID == 'NULL'  || $AktMuszak->korID == 'NINCS' ))
            throw new \Exception('Hibás értékelő kör ID!');
        if($AktMuszak->korID == 'NULL'  || $AktMuszak->korID == 'NINCS' )
            $AktMuszak->korID = null;


        if ($AktMuszak->pont < 0)
            throw new \Exception('A közösségi pontszám nagyobb, vagy egyenlő kell, hogy legyen, mint 0.');
        if ($AktMuszak->mospont < 0)
            throw new \Exception('A mosogatásért járó pontszám nagyobb, vagy egyenlő kell, hogy legyen, mint 0.');
        if ($AktMuszak->letszam < 1)
            throw new \Exception('A létszám nagyobb kell, hogy legyen, mint 0.');

        if (strlen($AktMuszak->musznev) > 230)
        {
            throw new \Exception('A műszaknév hossza maximum 230 karakter lehet.');
        }

        if (strlen($AktMuszak->megj) > 230)
        {
            throw new \Exception('A megjegyzés hossza maximum 230 karakter lehet.');
        }

        if (!verifyDate($AktMuszak->idokezd))
            throw new \Exception('A kezdési idő nem megfelelő. ' . $AktMuszak->idokezd);
        if (!verifyDate($AktMuszak->idoveg))
            throw new \Exception('A vég idő nem megfelelő.');

        if ($AktMuszak->idokezd >= $AktMuszak->idoveg)
            throw new \Exception('A vég idő később kell, hogy legyen, mint a kezdési idő!');


        $conn = Eszkozok\Eszk::initMySqliObject();


        if (!$conn)
            throw new \Exception('SQL hiba: $conn is \'false\'');

        $stmt = $conn->prepare("UPDATE `fxmuszakok` SET `kiirta` = ?, `musznev` = ?, `korid` = ?, `idokezd` = ?, `idoveg` = ?, `letszam` = ?, `pont` = ?, `mospont` = ? , `megj` = ? WHERE `fxmuszakok`.`ID` = ?;");
        if (!$stmt)
            throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

        $stmt->bind_param('ssissiddsi', $_SESSION['profilint_id'], $AktMuszak->musznev, $AktMuszak->korID, $AktMuszak->idokezd, $AktMuszak->idoveg, $AktMuszak->letszam, $AktMuszak->pont, $AktMuszak->mospont, $AktMuszak->megj, $AktMuszak->ID);
    }

    if ($stmt->execute())
    {
        ob_clean();
        if ($stmt->affected_rows == 0)
        {
            throw new Exception("Nem történt módosítás!");
        }

        try
        {

            if ($MuszakTorlesE)
                $logger->info('Műszak törölve! MUSZTOROL', [(isset($_SESSION['profilint_id'])) ? $_SESSION['profilint_id'] : 'No Internal ID', \Eszkozok\Eszk::get_client_ip_address(), $MuszakID]);
            else
                $logger->info('Műszak szerkesztve! MUSZSZERK', [(isset($_SESSION['profilint_id'])) ? $_SESSION['profilint_id'] : 'No Internal ID', \Eszkozok\Eszk::get_client_ip_address(), $MuszakID]);

        }
        catch (\Exception $e)
        {
        }

        die('siker4567');
    }
    else
    {
        throw new \Exception('Az SQL parancs végrehajtása nem sikerült.' . ' :' . $conn->error);
    }
}
catch (\Exception $e)
{
    ob_clean();
    //Eszkozok\Eszk::dieToErrorPage('2085: ' . $e->getMessage());
    echo 'Hiba: ' . $e->getMessage();
}