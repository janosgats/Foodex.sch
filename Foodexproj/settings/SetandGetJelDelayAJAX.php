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
        $muvelet = GetURLParam('muvelet');//hozzaadas, torles, modositas, lekeres
    else
        throw new \Exception('IsURLParamSet(\'muvelet\') is false! ');


    if (IsURLParamSet('ajaxuse') && GetURLParam('ajaxuse') == 1)
        error_reporting(0);//Disable displaying errors, to not to disturbe AJAX queryes processed by JSON in JS.


    $conn = Eszkozok\Eszk::initMySqliObject();
    if (!$conn)
        throw new \Exception('SQL hiba 1: $conn is \'false\'');

    switch ($muvelet)
    {
        case 'hozzaadas':
        {
            $stmt = $conn->prepare("INSERT INTO pontjeldelay (`minpont`, `delay`) VALUES (20, 60);");
            if (!$stmt->execute())
            {
                throw new Exception('SQL hiba 2: Nem sikerült a beillesztés. $stmt->execute() is \'false\'');
            }
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
        case 'lekeres':
            break;
    }

    $stmt = $conn->prepare("SELECT * FROM pontjeldelay ORDER BY minpont;");

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

    $ki = Array();
    $ki['error'] = '9087: ' . $e->getMessage();

    ob_clean();
    echo json_encode($ki);

    $conn->close();
}