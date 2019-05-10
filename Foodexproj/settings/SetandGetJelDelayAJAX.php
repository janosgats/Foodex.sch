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

    $new_inserted_row_id = null;

    switch ($muvelet)
    {
        case 'hozzaadas':
        {
            $stmt = $conn->prepare("INSERT INTO pontjeldelay (`minpont`, `delay`) VALUES (20, 3600);");
            if (!$stmt->execute())
            {
                throw new Exception('SQL hiba 3: Nem sikerült a beillesztés. $stmt->execute() is \'false\'');
            }

            $new_inserted_row_id = $stmt->insert_id;
            break;
        }
        case 'torles':
        {
            if (!IsURLParamSet('id'))
            {
                throw new Exception('Hiányzó paraméter: id');
            }

            if (!is_numeric($id = GetURLParam('id')))
                throw new Exception('Hibás paraméter: az id nem egy szám!');


            $stmt = $conn->prepare("DELETE FROM pontjeldelay WHERE id = ?;");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute() || $conn->affected_rows != 1)
            {
                throw new Exception('SQL hiba 4: Nem sikerült a törlés. $stmt->execute() is \'false\' or affected_rows() != 1');
            }

            break;
        }
        case 'modositas':
        {
            if (!IsURLParamSet('id'))
                throw new Exception('Hiányzó paraméter: id');
            if (!is_numeric($id = GetURLParam('id')))
                throw new Exception('Hibás paraméter: az id nem egy szám!');

            if (!IsURLParamSet('minpont'))
                throw new Exception('Hiányzó paraméter: minpont');
            if (!is_numeric($minpont = GetURLParam('minpont')))
                throw new Exception('Hibás paraméter: a minpont nem egy szám!');

            if (!IsURLParamSet('delay'))
                throw new Exception('Hiányzó paraméter: delay');
            if (!is_numeric($delay = GetURLParam('delay')))
                throw new Exception('Hibás paraméter: a delay nem egy szám!');

            $stmt = $conn->prepare("UPDATE pontjeldelay SET minpont = ?, delay = ? WHERE id = ?;");
            $stmt->bind_param("dii", $minpont, $delay, $id);
            if (!$stmt->execute())
            {
                throw new Exception('SQL hiba 4: Nem sikerült a módosítás. $stmt->execute() is \'false\'');
            }
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

        if ($new_inserted_row_id != null)
            $ki['new_inserted_row_id'] = $new_inserted_row_id;

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