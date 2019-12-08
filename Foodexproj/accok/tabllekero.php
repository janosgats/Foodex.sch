<?php
/**
 * Az accok táblázatát frissítő AJAX requesthez
 */

session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';

\Eszkozok\LoginValidator::AdminJog_DiesToErrorrPage();

ob_start();
$conn;
try {
    $keresett = '';
    $showArchived = 0;

    if (isset($_REQUEST['keresett']) && $_REQUEST['keresett'] != '')
        $keresett = $_REQUEST['keresett'];

    if (isset($_REQUEST['show_archived']) && $_REQUEST['show_archived'] == '1')
        $showArchived = 1;

    $conn = \Eszkozok\Eszk::initMySqliObject();
    $stmt;
    if ($keresett == '') {
        $stmt = $conn->prepare(" SELECT internal_id, nev, archiv, belephet, fxtag, adminjog, muszjeljog, pontlatjog, korertekelesek.grouped_korok AS grouped_korertekelesek FROM `fxaccok` LEFT JOIN (SELECT ertekelo, GROUP_CONCAT(korid ORDER BY korid ASC) AS grouped_korok FROM korertekelok GROUP BY ertekelo) AS korertekelesek ON korertekelesek.ertekelo = fxaccok.internal_id WHERE (`archiv`=0 OR 1=?) ORDER BY belephet ASC, nev ASC;");
        $stmt->bind_param('i', $showArchived);
    } else {
        $stmt = $conn->prepare(" SELECT internal_id, nev, archiv, belephet, fxtag,  adminjog, muszjeljog, pontlatjog, korertekelesek.grouped_korok AS grouped_korertekelesek  FROM `fxaccok` LEFT JOIN (SELECT ertekelo, GROUP_CONCAT(korid ORDER BY korid ASC) AS grouped_korok FROM korertekelok GROUP BY ertekelo) AS korertekelesek ON korertekelesek.ertekelo = fxaccok.internal_id WHERE  (`archiv`=0 OR 1=?) AND `nev` LIKE CONCAT('%', ? , '%') ORDER BY belephet ASC,  nev ASC;");
        $stmt->bind_param('is', $showArchived, $keresett);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        ob_get_clean();

        $fullres = array();
        while ($row = $result->fetch_assoc()) {
            $fullres[] = $row;
        }
        echo json_encode($fullres);

        $conn->close();

        die();
    } else {
        throw new Exception('$stmt->execute() is false!');
    }

} catch (\Exception $e) {
    \Eszkozok\Eszk::dieToErrorPage('4563: ' . $e->getMessage());
} finally {
    try {
        $conn->close();
    } catch (\Exception $ex) {
    }
}