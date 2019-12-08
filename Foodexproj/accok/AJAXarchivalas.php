<?php

ob_start();

session_start();
error_reporting(0);

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';

$conn = new mysqli();
try {
    \Eszkozok\LoginValidator::AdminJog_ThrowsException();

    if (!isset($_REQUEST['int_id']) || $_REQUEST['int_id'] == '' || !isset($_REQUEST['action']) || $_REQUEST['action'] == '')
        throw new \Exception('Hiányzó paraméterek!');

    $action = $_REQUEST['action'];
    $internal_id_of_Acc = $_REQUEST['int_id'];

    if (Eszkozok\Eszk::GetTaroltProfilAdat($internal_id_of_Acc) == null)
        throw new \Exception('Nem létező felhasználót próbálsz archiválni!');

    if ($action == 'archival') {
        $conn = \Eszkozok\Eszk::initMySqliObject();
        $stmt = $conn->prepare("UPDATE fxaccok SET `archiv`=1, `belephet`=0 WHERE `internal_id`=?;");
    } else if ($action == 'visszavon') {
        $conn = \Eszkozok\Eszk::initMySqliObject();
        $stmt = $conn->prepare("UPDATE fxaccok SET `archiv`=0 WHERE `internal_id`=?;");
    } else {
        throw new \Exception("Definiálatlan művelet: $action");
    }

    $stmt->bind_param('s', $internal_id_of_Acc);

    if (!$stmt->execute())
        throw new \Exception('!$stmt->execute() is false!');

    $ki = [];
    $ki['status'] = 'siker2345';
    $ki['internal_id'] = $internal_id_of_Acc;
    $ki['action'] = $action;

    try {
        $conn->close();
    } catch (\Exception $e) {
    }

    ob_clean();
    echo json_encode($ki);
} catch (\Exception $e) {
    $ki = Array();
    $ki['status'] = 'hiba';
    $ki['error'] = '9189: ' . $e->getMessage();

    ob_clean();
    echo json_encode($ki);
    $conn->close();
}