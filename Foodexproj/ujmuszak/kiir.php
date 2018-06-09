<?php
session_start();

set_include_path(getcwd());
include_once '../Eszkozok/Eszk.php';
include_once '../Eszkozok/Muszak.php';


function GetParam($parameterneve)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        return $_POST[$parameterneve];
    }
    else
    {
        return $_GET[$parameterneve];
    }
}

function IsParamSet($parameterneve)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        return isset($_POST[$parameterneve]);
    }
    else
    {
        return isset($_GET[$parameterneve]);
    }
}

function verifyDate($date, $strict = true)
{
    $dateTime = DateTime::createFromFormat('Y/m/d G:i', $date);
    if ($strict)
    {
        $errors = DateTime::getLastErrors();
        if (!empty($errors['warning_count']))
        {
            return false;
        }
    }
    return $dateTime !== false;
}

try
{


    if (!isset($_SESSION['profilint_id']))
        Eszkozok\Eszk::RedirectUnderRoot('');

    $AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

    if ($AktProfil->getUjMuszakJog() != 1)
        Eszkozok\Eszk::dieToErrorPage('2077: Nincs jogosultságod új műszakot kiírni!');


    $internal_id = $_SESSION['profilint_id'];


    $AktMuszak = new \Eszkozok\Muszak();
    $AktMuszak->kiirta = $internal_id;

    if (IsParamSet('musznev'))
        $AktMuszak->musznev = GetParam('musznev');
    if (IsParamSet('letszam'))
        $AktMuszak->letszam = GetParam('letszam');
    if (IsParamSet('pont'))
        $AktMuszak->pont = GetParam('pont');
    if (IsParamSet('idokezd'))
        $AktMuszak->idokezd = GetParam('idokezd');
    if (IsParamSet('idoveg'))
        $AktMuszak->idoveg = GetParam('idoveg');

    if (!is_numeric($AktMuszak->pont))
        throw new \Exception('A közösségi pontszám nem egy szám.');
    if (!is_numeric($AktMuszak->letszam))
        throw new \Exception('A létszám nem egy szám.');
    if ($AktMuszak->pont < 0)
        throw new \Exception('A közösségi pontszám nagyobb, vagy egyenlő kell, hogy legyen, mint 0.');
    if ($AktMuszak->letszam < 1)
        throw new \Exception('A létszám nagyobb kell, hogy legyen, mint 0.');

    if (strlen($AktMuszak->musznev) > 230)
    {
        throw new \Exception('A műszaknév hossza maximum 230 karakter lehet.');
    }

    if (!verifyDate($AktMuszak->idokezd))
        throw new \Exception('A kezdési idő nem megfelelő.');
    if (!verifyDate($AktMuszak->idoveg))
        throw new \Exception('A vég idő nem megfelelő.');


    $conn = Eszkozok\Eszk::initMySqliObject();


    if (!$conn)
        throw new \Exception('SQL hiba: $conn is \'false\'');

    $stmt = $conn->prepare("INSERT INTO `fxmuszakok` (`kiirta`, `musznev`, `idokezd`, `idoveg`, `letszam`, `pont`) VALUES (?, ?, ?, ?, ?, ?);");
    if (!$stmt)
        throw new \Exception('SQL hiba: $stmt is \'false\'');

    $stmt->bind_param('ssssii', $AktMuszak->kiirta, $AktMuszak->musznev, $AktMuszak->idokezd, $AktMuszak->idoveg, $AktMuszak->letszam, $AktMuszak->pont);




    if ($stmt->execute())
    {
        //ob_clean();
        die('siker4567');
    }
    else
    {
        throw new \Exception('Az SQL parancs végrehajtása nem sikerült.');
    }
}
catch (\Exception $e)
{
    ob_clean();
    Eszkozok\Eszk::dieToErrorPage('2085: ' . $e->getMessage());
}