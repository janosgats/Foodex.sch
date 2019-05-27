<?php
session_start();

set_include_path(getcwd());
require_once '../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once '../Eszkozok/entitas/Muszak.php';


require_once __DIR__ . '/../Eszkozok/param.php';

try
{


    \Eszkozok\LoginValidator::AdminJog_DiesToErrorrPage();


    $kompid = 'URL PARAM IS NOT SET';



    $Muvelet = '';

    if (IsURLParamSet('muv'))
        $Muvelet = GetURLParam('muv');



    if ($Muvelet == 'edit' || $Muvelet == 'delete')
    {
        if (IsURLParamSet('kompid'))
            $kompid = GetURLParam('kompid');
    }

    switch ($Muvelet)
    {
        case 'submit':
        case 'edit':
        {
            $int_id;
            $pont;
            $megj;


            if (IsURLParamSet('int_id'))
                $int_id = GetURLParam('int_id');
            if (IsURLParamSet('pont'))
                $pont = GetURLParam('pont');
            if (IsURLParamSet('megj'))
                $megj = GetURLParam('megj');

            $KompProfil = \Eszkozok\Eszk::GetTaroltProfilAdat($int_id);//Ha nincs ilyen internal_id, akkor ez kilépteti az error oldalra

            if (!is_numeric($pont))
                throw new \Exception('A közösségi pontszám nem egy szám.');

            if (strlen($megj) > 5000)
            {
                throw new \Exception('A megjegyzés hossza maximum 5000 karakter lehet.');
            }


            $conn = Eszkozok\Eszk::initMySqliObject();


            if (!$conn)
                throw new \Exception('SQL hiba: $conn is \'false\'');


            if ($Muvelet == 'submit')
            {
                $stmt = $conn->prepare("INSERT INTO `kompenz` (`internal_id`, `pont`, `megj`, `ido`) VALUES (?, ?, ?, ?);");

                if (!$stmt)
                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                date_default_timezone_set("Europe/Budapest");
                $ido = (new DateTime(null, new \DateTimeZone(date_default_timezone_get() ?: 'UTC+1')))->format("Y-m-d H:i:s");

                $stmt->bind_param('sdss', $int_id, $pont, $megj, $ido);
            }
            else if ($Muvelet == 'edit')
            {
                $stmt = $conn->prepare("UPDATE `kompenz` SET `internal_id` = ?, `pont` = ?, `megj` = ? WHERE `kompenz`.`ID` = ?;");

                if (!$stmt)
                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                $stmt->bind_param('sdsi', $int_id, $pont, $megj, $kompid);
            }

        }
            break;

        case 'delete':
        {
            $conn = Eszkozok\Eszk::initMySqliObject();

            if (!$conn)
                throw new \Exception('SQL hiba: $conn is \'false\'');

            $stmt = $conn->prepare("DELETE FROM `kompenz` WHERE `kompenz`.`ID` = ?;");

            if (!$stmt)
                throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

            $stmt->bind_param('i', $kompid);
        }
            break;

        default:
            throw new \Exception('A kért művelet (' . htmlspecialchars($Muvelet) . ') nincs definiálva.');
    }


    if ($stmt->execute())
    {
        ob_clean();

        if ($stmt->affected_rows == 0)
        {
            throw new Exception("Nem történt módosítás!");
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