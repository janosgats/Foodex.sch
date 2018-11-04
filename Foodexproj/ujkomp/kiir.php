<?php
session_start();

set_include_path(getcwd());
require_once '../Eszkozok/Eszk.php';
require_once '../Eszkozok/Muszak.php';


require_once __DIR__ . '/../Eszkozok/param.php';

try
{


    \Eszkozok\Eszk::ValidateLogin();

    $AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

    if ($AktProfil->getUjMuszakJog() != 1)
        Eszkozok\Eszk::dieToErrorPage('2077: Nincs jogosultságod kompenzálni!');

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

    $stmt = $conn->prepare("INSERT INTO `kompenz` (`internal_id`, `pont`, `megj`) VALUES (?, ?, ?);");
    if (!$stmt)
        throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

    $stmt->bind_param('sds', $int_id, $pont, $megj);


    if ($stmt->execute())
    {
        ob_clean();
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