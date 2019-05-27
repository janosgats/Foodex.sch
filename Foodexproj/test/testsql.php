<?php


require_once 'Eszkozok/Eszk.php';

$conn = Eszkozok\Eszk::initMySqliObject();

$stmt = $conn->prepare("SELECT * FROM fxmuszakok WHERE ID = 17");
if (!$stmt)
    throw new \Exception('SQL hiba: $stmt is \'false\'');

$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
var_dump($row);