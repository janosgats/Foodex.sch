<?php
/**
 * A körök táblázatát frissítő AJAX requesthez
 */

session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';

\Eszkozok\LoginValidator::AccountSignedIn();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

ob_start();
$conn;
try
{


$keresett = '';

if (isset($_REQUEST['keresett']) && $_REQUEST['keresett'] != '')
    $keresett = $_REQUEST['keresett'];

$conn = \Eszkozok\Eszk::initMySqliObject();
$stmt;
if ($keresett == '')
    $stmt = $conn->prepare(" SELECT internal_id, nev, adminjog, muszjeljog FROM `fxaccok` ORDER BY nev ASC;");
else
{
    $stmt = $conn->prepare(" SELECT internal_id, nev, adminjog, muszjeljog FROM `fxaccok` WHERE `nev` LIKE CONCAT('%', ? , '%') ORDER BY nev ASC;");
    $stmt->bind_param('s', $keresett);
}

    if($stmt->execute())
    {
        $result = $stmt->get_result();
        ob_get_clean();

        $fullres = array();
        while( $row = $result->fetch_assoc()){
            $fullres[] = $row; // Inside while loop
        }
        echo json_encode($fullres);

        $conn->close();

        die();
    }
    else
    {
        throw new Exception('$stmt->execute() is false!');
    }

}
catch (\Exception $e)
{
    \Eszkozok\Eszk::dieToErrorPage('4563: ' . $e->getMessage());
}
finally
{
    try{$conn->close();}catch (\Exception $ex){}
}