<?php
session_start();

set_include_path(getcwd());
require_once '../Eszkozok/Eszk.php';
require_once '../Eszkozok/Muszak.php';


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


    \Eszkozok\Eszk::ValidateLogin();

    $AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

    if ($AktProfil->getUjMuszakJog() != 1)
        Eszkozok\Eszk::dieToErrorPage('19077: Nincs jogosultságod a műszak szerkesztésére!');


    $internal_id = $_SESSION['profilint_id'];


    $AktMuszak = new \Eszkozok\Muszak();
    $AktMuszak->kiirta = $internal_id;

    if (IsURLParamSet('muszid'))
        $AktMuszak->ID = GetURLParam('muszid');

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

    if (!is_numeric($AktMuszak->pont))
        throw new \Exception('A közösségi pontszám nem egy szám.');
    if (!is_numeric($AktMuszak->mospont))
        throw new \Exception('A mosogatásért járó pontszám nem egy szám.');
    if (!is_numeric($AktMuszak->letszam))
        throw new \Exception('A létszám nem egy szám.');
    if (!is_numeric($AktMuszak->ID))
        throw new \Exception('Az ID nem egy szám.');

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

    if (!verifyDate($AktMuszak->idokezd))
        throw new \Exception('A kezdési idő nem megfelelő. ' . $AktMuszak->idokezd);
    if (!verifyDate($AktMuszak->idoveg))
        throw new \Exception('A vég idő nem megfelelő.');


    $conn = Eszkozok\Eszk::initMySqliObject();


    if (!$conn)
        throw new \Exception('SQL hiba: $conn is \'false\'');

    $stmt = $conn->prepare("UPDATE `fxmuszakok` SET `musznev` = ?, `idokezd` = ?, `idoveg` = ?, `letszam` = ?, `pont` = ?, `mospont` = ? WHERE `fxmuszakok`.`ID` = ?;");
    if (!$stmt)
        throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

    $stmt->bind_param('ssssiss', $AktMuszak->musznev, $AktMuszak->idokezd, $AktMuszak->idoveg, $AktMuszak->letszam, $AktMuszak->pont, $AktMuszak->mospont, $AktMuszak->ID);


    if ($stmt->execute())
    {
        //ob_clean();
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