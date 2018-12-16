<?php
date_default_timezone_set("Europe/Budapest");
echo (new DateTime())->format("Y-m-d H:i:s");

require_once __DIR__ . '/Eszkozok/Eszk.php';

echo '1';

$conn = Eszkozok\Eszk::initMySqliObject();


if(!conn)
    die('$conn is false!');

echo '2';
$stmt = $conn->prepare("select * from mysql.user;");
//$stmt = $conn->prepare("CREATE TABLE test1 (id int(6));");
//$stmt = $conn->prepare("SHOW TABLES;");

echo '3';

if(!$stmt)
    die('$stmt is false!' . $conn->error);

if ($stmt->execute())
{
    echo '4';

    $res = $stmt->get_result();
    echo 'x';
    while ($row = $res->fetch_assoc())
        var_dump($row);
}
else
    echo "<r><br>HIBA!!!!!";

