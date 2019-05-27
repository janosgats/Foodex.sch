<?php

session_start();
error_reporting(0);

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';

$conn;
try
{
    \Eszkozok\LoginValidator::AdminJog_ThrowsException();

    if (!isset($_REQUEST['int_id']) || !isset($_REQUEST['adminjog']) || !isset($_REQUEST['muszjeljog']) || $_REQUEST['int_id'] == '' || $_REQUEST['adminjog'] == '' || $_REQUEST['muszjeljog'] == '')
        throw new \Exception('Hiányzó paraméterek!');

    $internal_id_of_Acc = $_REQUEST['int_id'];
    $adminjogtoset = $_REQUEST['adminjog'];
    $muszjeljogtoset = $_REQUEST['muszjeljog'];

    if (!($adminjogtoset === '0' || $adminjogtoset === '1'))
        throw new \Exception('Hibás paraméter: adminjog!');

    if (!($muszjeljogtoset === '0' || $muszjeljogtoset === '1'))
        throw new \Exception('Hibás paraméter: muszjeljog!');


    $conn = \Eszkozok\Eszk::initMySqliObject();

    $stmt = $conn->prepare("UPDATE `fxaccok` SET `adminjog` = ?, `muszjeljog` = ? WHERE `internal_id` = ?;");

    $stmt->bind_param('iis', $adminjogtoset, $muszjeljogtoset, $internal_id_of_Acc);

    if (!$stmt->execute())
        throw new \Exception('!$stmt->execute() is false!');

    if ($stmt->affected_rows != 1)
        throw new \Exception('!$stmt->affected_rows != 1 !');

    $ki = [];
    $ki['status'] = 'siker3456';
    $ki['internal_id'] = $internal_id_of_Acc;
    $ki['adminjog'] = $adminjogtoset;
    $ki['muszjeljog'] = $muszjeljogtoset;

    ob_clean();
    echo json_encode($ki);
    $conn->close();

}
catch (\Exception $e)
{

    $ki = Array();
    $ki['status'] = 'hiba';
    $ki['error'] = '9087: ' . $e->getMessage();

    ob_clean();
    echo json_encode($ki);
    $conn->close();

}