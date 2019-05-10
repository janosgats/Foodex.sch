<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/param.php';

try
{
    \Eszkozok\Eszk::ValidateLogin();

    $AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

    if ($AktProfil->getAdminJog() != 1)
        throw new \Exception('Nincs jogosultságod módosítani a  beállításokat!');

    if (IsURLParamSet('muvelet'))
        $muvelet = GetURLParam('$muvelet');//hozzaadas, torles, modositas
    else
        throw new \Exception('IsURLParamSet(\'muvelet\') is false! ');


    $conn = Eszkozok\Eszk::initMySqliObject();
    if (!$conn)
        throw new \Exception('SQL hiba 1: $conn is \'false\'');

    switch ($muvelet)
    {
        case 'hozzaadas':
        {

            break;
        }
        case 'torles':
        {

            break;
        }
        case 'modositas':
        {

            break;
        }
    }

    $stmt = $conn->prepare("SELECT * FROM pontjeldelay;");

    if ($stmt->execute())
    {
        $result = $stmt->get_result();

        $ki = $result->fetch_all(MYSQLI_ASSOC);

        ob_clean();
        echo json_encode($ki);
        $conn->close();
    }
    else
        throw new \Exception('SQL hiba 2: $stmt->execute() is \'false\'');

}
catch (\Exception $e)
{
    ob_clean();
    //Eszkozok\Eszk::dieToErrorPage('2085: ' . $e->getMessage());
    echo '9087: ' . $e->getMessage();
}