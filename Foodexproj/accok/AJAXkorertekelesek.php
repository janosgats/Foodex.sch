<?php

ob_start();

session_start();
error_reporting(0);

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';

$conn = new mysqli();
try
{
    \Eszkozok\LoginValidator::AdminJog_ThrowsException();

    if (!isset($_REQUEST['int_id']) || $_REQUEST['int_id'] == '' || !isset($_REQUEST['ertekelesjogokJSON']) || $_REQUEST['ertekelesjogokJSON'] == '')
        throw new \Exception('Hiányzó paraméterek!');

    $internal_id_of_Acc = $_REQUEST['int_id'];
    if (Eszkozok\Eszk::GetTaroltProfilAdat($internal_id_of_Acc) == null)
        throw new \Exception('Nem létező felhasználó adatait próbálod módosítani!');

    $korokJogokToDelete = [];
    $korokJogokToAdd = [];

    $ertekelesjogokBE = json_decode($_REQUEST['ertekelesjogokJSON']);

    $conn = \Eszkozok\Eszk::initMySqliObject();

    foreach ($ertekelesjogokBE as $key => $value)
    {
        if ($value == 1)
            $korokJogokToAdd[] = $conn->escape_string($key);
        else if ($value == 0)
            $korokJogokToDelete[] = $conn->escape_string($key);
    }

    if (count($korokJogokToDelete) > 0)
    {
        $stmt = $conn->prepare("DELETE FROM `korertekelok` WHERE `ertekelo` = ? AND `korid` IN (" . implode(',', $korokJogokToDelete) . ");");

        $stmt->bind_param('s', $internal_id_of_Acc);

        if (!$stmt->execute())
            throw new \Exception('!$stmt->execute() is false!');
    }

    $stmt = $conn->prepare("INSERT INTO `korertekelok` (`ertekelo`,`korid`) VALUES (?, ?) ON DUPLICATE KEY UPDATE korid=korid;");

    foreach ($korokJogokToAdd as $korIdToAdd)
    {
        $stmt->bind_param('si', $internal_id_of_Acc, $korIdToAdd);

        if (!$stmt->execute())
            throw new \Exception('!$stmt->execute() is false!');
    }

    $ki = [];
    $ki['status'] = 'siker2345';
    $ki['internal_id'] = $internal_id_of_Acc;
    $ki['korertekelesek'] = $ertekelesjogokBE;

    try
    {
        $conn->close();
    }
    catch (\Exception $e)
    {
    }

    ob_clean();
    echo json_encode($ki);
}
catch (\Exception $e)
{

    $ki = Array();
    $ki['status'] = 'hiba';
    $ki['error'] = '9089: ' . $e->getMessage();

    ob_clean();
    echo json_encode($ki);
    $conn->close();

}