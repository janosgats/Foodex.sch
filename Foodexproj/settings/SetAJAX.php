<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/param.php';
require_once __DIR__ . '/../Eszkozok/AJAXhost.php';


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
        Eszkozok\Eszk::dieToErrorPage('9077: Nincs jogosultságod módosítani a pontozási beállításokat!');

    if (IsURLParamSet('beallID'))
        $beallID = GetURLParam('beallID');
    else
        Eszkozok\Eszk::dieToErrorPage('9078: IsURLParamSet(\'beallID\') is false! ');


    $conn = Eszkozok\Eszk::initMySqliObject();
    if (!$conn)
        throw new \Exception('SQL hiba: $conn is \'false\'');

    switch($beallID)
    {
        case 'pontidoszak':
        {

            if (IsURLParamSet('pontidokezd'))
                $pontidokezd = GetURLParam('pontidokezd');
            if (IsURLParamSet('pontidoveg'))
                $pontidoveg = GetURLParam('pontidoveg');

            if (!verifyDate($pontidokezd))
                throw new \Exception('A kezdési idő nem megfelelő.');
            if (!verifyDate($pontidoveg))
                throw new \Exception('A vég idő nem megfelelő.');

            \Eszkozok\Eszk::SetGlobalSettingsWithConn('pontozasi_idoszak_kezdete', $pontidokezd, $conn);
            \Eszkozok\Eszk::SetGlobalSettingsWithConn('pontozasi_idoszak_vege', $pontidoveg, $conn);

            $conn->close();
            QuitHost('siker4567');

            break;
        }
    }


}
catch (\Exception $e)
{
    ob_clean();
    //Eszkozok\Eszk::dieToErrorPage('2085: ' . $e->getMessage());
    echo 'Hiba: ' . $e->getMessage();
}