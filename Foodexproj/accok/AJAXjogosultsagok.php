<?php

session_start();
error_reporting(0);

require_once __DIR__ . '/../Eszkozok/Eszk.php';

$conn;
try
{
    \Eszkozok\Eszk::ValidateLogin();

    $AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

    if ($AktProfil->getAdminJog() != 1)
        throw new \Exception('Nincs jogosultságod módosítani a  beállításokat!');

    if(!isset($_REQUEST['int_id']) || !isset($_REQUEST['adminjog']) || $_REQUEST['int_id'] == '' || $_REQUEST['adminjog'] == '')
        throw new \Exception('Hiányzó paraméterek!');

    $adminjogtoset = $_REQUEST['adminjog'];
    $internal_id_of_Acc = $_REQUEST['int_id'];

    if(!($adminjogtoset === '0' || $adminjogtoset === '1'))
        throw new \Exception('Hibás paraméter: adminjog!');


    $conn = \Eszkozok\Eszk::initMySqliObject();

    $stmt = $conn->prepare("UPDATE `fxaccok` SET `adminjog` = ? WHERE `internal_id` = ?;");

    $stmt->bind_param('is', $adminjogtoset, $internal_id_of_Acc);

    if(!$stmt->execute())
        throw new \Exception('!$stmt->execute() is false!');

    if($stmt->affected_rows != 1)
        throw new \Exception('!$stmt->affected_rows != 1 !');

    $ki = [];
    $ki['status'] = 'siker3456';
    $ki['internal_id'] = $internal_id_of_Acc;
    $ki['adminjog'] = $adminjogtoset;

    ob_clean();
    echo json_encode($ki);
    $conn->close();

}
catch (\Exception $e)
{

    $ki = Array();
    $ki['status'] = 'hiba';
    $ki['error']   = '9087: ' . $e->getMessage();

    ob_clean();
    echo json_encode($ki);
    $conn->close();

}