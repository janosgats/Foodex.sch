<?php
session_start();

$internal_id = $_SESSION['profilint_id'];
unset($_SESSION['profilint_id']);


include_once __DIR__ . '/../Eszkozok/Eszk.php';

try
{


    $conn = \Eszkozok\Eszk::initMySqliObject();

    if (!$conn)
        throw new \Exception('c1');

    $stmt = $conn->prepare("UPDATE `fxaccok` SET `session_token` = 'kijelentkezve' WHERE `fxaccok`.`internal_id` = ?");
    $stmt->bind_param('s', $internal_id);


    if ($stmt->execute())
    {

    }
    else
        throw new \Exception('c2');


    Eszkozok\Eszk::RedirectUnderRoot('');

}
catch (\Exception $e)
{
    \Eszkozok\Eszk::dieToErrorPage('35465: A biztonságos kijelentkezés nem (teljesen) sikerült. Érdemes ismét be-, majd kijelentkezned! => ' . $e->getMessage());
}

