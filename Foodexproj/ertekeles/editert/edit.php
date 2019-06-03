<?php
session_start();
error_reporting(0);

set_include_path(getcwd());
require_once __DIR__ . '/../../Eszkozok/Eszk.php';
require_once __DIR__ . '/../../Eszkozok/LoginValidator.php';
require_once __DIR__ . '/../../Eszkozok/MonologHelper.php';
require_once __DIR__ . '/../../Eszkozok/entitas/Ertekeles.php';

$conn = new \mysqli();

function mentes_sec_checks(\mysqli $conn, $muvelet, $muszid, $ertekelt)
{
    if ($muvelet == 'letrehozas')
    {
        ///////////CHECK: Van-e joga ehhez a műszakhoz?/////////////////
        if (($muszak = \Eszkozok\Eszk::GetTaroltMuszakAdatWithConn($muszid, false, $conn)) == null)
            throw new \Exception('Hiba a műszak fetchelése során!');

        if (!in_array($muszak->korID, \Eszkozok\LoginValidator::GetErtekeloKorokIdk()))
            return false;
    }
    else
    {
        ///////////CHECK: Ő írta-e ezt az értékelést?/////////////////

        $stmt = $conn->prepare("SELECT ertekelo FROM ertekelesek WHERE ertekelesek.muszid = ?  AND ertekelesek.ertekelt = ? AND ertekelesek.ertekelo = ?;");
        $stmt->bind_param('iss', $muszid, $ertekelt, $_SESSION['profilint_id']);

        if (!$stmt->execute())
            throw new \Exception('$stmt->execute() c0 is false!');

        $res = $stmt->get_result();

        if ($res->num_rows != 1)
            throw new Exception('Nem te írtad ezt az értékelést.');
    }

    ///////////CHECK: Ez a foodexes vitte-e a műszakot és az már véget ért-e?/////////////////

    $stmt = $conn->prepare("SELECT fxjelentk.jelentkezo FROM   fxjelentk INNER JOIN
                                            (
                                            SELECT fxmuszakok.korid, muszid, idoveg, letszam, GROUP_CONCAT(jelentkezo ORDER BY jelido ASC) AS grouped_jelentkezo
                                            FROM     fxjelentk
                                            JOIN fxmuszakok ON fxjelentk.muszid = fxmuszakok.ID
                                            WHERE fxjelentk.status = 1
                                            GROUP BY muszid
                                            ) AS group_max
                                            ON fxjelentk.muszid = group_max.muszid AND FIND_IN_SET(jelentkezo, grouped_jelentkezo) <= group_max.letszam
                                            WHERE status = 1 AND fxjelentk.muszid = ?  AND fxjelentk.jelentkezo = ?
                                            AND group_max.idoveg < NOW()
                                            ORDER BY fxjelentk.muszid, fxjelentk.jelido ASC;");
    $stmt->bind_param('is', $muszid, $ertekelt);

    if (!$stmt->execute())
        throw new \Exception('$stmt->execute() c1 is false!');

    $res = $stmt->get_result();

    if ($res->num_rows != 1)
        throw new \Exception('Ezt a műszakot nem ez a foodexes vitte!');

    return true;
}


function AktualisProfilIrtaEztAzErtekelest_sec_checks(\mysqli $conn, $ert_id)
{
    ///////////CHECK: Ő írta-e ezt az értékelést?/////////////////

    $stmt = $conn->prepare("SELECT ertekelo FROM ertekelesek WHERE id = ?;");
    $stmt->bind_param('i', $ert_id);

    if (!$stmt->execute())
        throw new \Exception('$stmt->execute() c2 is false!');

    $res = $stmt->get_result();

    if ($res->num_rows != 1)
        throw new Exception('Nem te írtad ezt az értékelést.');


    if ($res->fetch_assoc()['ertekelo'] == $_SESSION['profilint_id'])
        return true;

    throw new Exception('Nem te írtad ezt az értékelést.');
}

try
{
    $conn = \Eszkozok\Eszk::initMySqliObject();

    \Eszkozok\LoginValidator::Ertekelo_DiesToErrorrPage();

    $logger = new \MonologHelper('ertekeles/editert/edit.php');

    $Muvelet = '';
    if (isset($_REQUEST['muvelet']))
        $Muvelet = $_REQUEST['muvelet'];


    switch ($Muvelet)
    {
        case 'letrehozas':
        case 'modositas':
        {
            $MentendoErtekeles = new \Eszkozok\Ertekeles();

            $MentendoErtekeles->ertekelo = $_SESSION['profilint_id'];


            if (isset($_REQUEST['muszid']) && $_REQUEST['muszid'] != '')
                $MentendoErtekeles->muszid = $_REQUEST['muszid'];

            if (isset($_REQUEST['ertekelt_int_id']) && $_REQUEST['ertekelt_int_id'] != '')
                $MentendoErtekeles->ertekelt = $_REQUEST['ertekelt_int_id'];


            if ($MentendoErtekeles->muszid == null || $MentendoErtekeles->ertekelt == null)
                throw new \Exception('Hiányzó paraméterek. Nincs megadva műszak, vagy értékelt profil!');

            if (!mentes_sec_checks($conn, $Muvelet, $MentendoErtekeles->muszid, $MentendoErtekeles->ertekelt))
                throw new \Exception('Valami nem stimmel az értékeléssel.');


            if (isset($_REQUEST['e_pontossag']) && $_REQUEST['e_pontossag'] != '')
                $MentendoErtekeles->e_pontossag = $_REQUEST['e_pontossag'];

            if (isset($_REQUEST['e_penzkezeles']) && $_REQUEST['e_penzkezeles'] != '')
                $MentendoErtekeles->e_penzkezeles = $_REQUEST['e_penzkezeles'];

            if (isset($_REQUEST['e_szakertelem']) && $_REQUEST['e_szakertelem'] != '')
                $MentendoErtekeles->e_szakertelem = $_REQUEST['e_szakertelem'];

            if (isset($_REQUEST['e_dughatosag']) && $_REQUEST['e_dughatosag'] != '')
                $MentendoErtekeles->e_dughatosag = $_REQUEST['e_dughatosag'];

            if (isset($_REQUEST['e_szoveg']) && $_REQUEST['e_szoveg'] != '')
                $MentendoErtekeles->e_szoveg = $_REQUEST['e_szoveg'];

            if ($MentendoErtekeles->e_pontossag == null && $MentendoErtekeles->e_penzkezeles == null && $MentendoErtekeles->e_szakertelem == null && $MentendoErtekeles->e_dughatosag == null && $MentendoErtekeles->e_szoveg == null)
                throw new \Exception('Értékelésedben semmit sem értékeltél. Így nincs értelme menteni.');


            if ($Muvelet == 'modositas')
            {

                $stmt = $conn->prepare("UPDATE ertekelesek SET e_szoveg = ?,e_pontossag = ?,e_penzkezeles = ?,e_szakertelem = ?,e_dughatosag = ? WHERE  muszid = ? AND ertekelt = ? AND ertekelo = ?;");
                $stmt->bind_param('sddddiss', $MentendoErtekeles->e_szoveg, $MentendoErtekeles->e_pontossag, $MentendoErtekeles->e_penzkezeles,
                    $MentendoErtekeles->e_szakertelem, $MentendoErtekeles->e_dughatosag, $MentendoErtekeles->muszid, $MentendoErtekeles->ertekelt, $MentendoErtekeles->ertekelo);

                if (!$stmt->execute())
                    throw new \Exception('$stmt->execute() is false: m2 ' . $stmt->error);


                $logger->info('Értékelés módosítva', [(isset($_SESSION['profilint_id'])) ? $_SESSION['profilint_id'] : 'No Internal ID', \Eszkozok\Eszk::get_client_ip_address(), $conn->insert_id]);
            }
            else if ($Muvelet == 'letrehozas')
            {
                $stmt = $conn->prepare("INSERT INTO ertekelesek SET ertekelo = ?,ertekelt = ?,muszid = ?,e_szoveg = ?,e_pontossag = ?,e_penzkezeles = ?,e_szakertelem = ?,e_dughatosag = ?;");
                $stmt->bind_param('ssisdddd', $MentendoErtekeles->ertekelo, $MentendoErtekeles->ertekelt, $MentendoErtekeles->muszid, $MentendoErtekeles->e_szoveg, $MentendoErtekeles->e_pontossag,
                    $MentendoErtekeles->e_penzkezeles, $MentendoErtekeles->e_szakertelem, $MentendoErtekeles->e_dughatosag);

                if (!$stmt->execute())
                    throw new \Exception('$stmt->execute() is false: m3 ' . $stmt->error);

                $logger->info('Új Értékelés mentve', [(isset($_SESSION['profilint_id'])) ? $_SESSION['profilint_id'] : 'No Internal ID', \Eszkozok\Eszk::get_client_ip_address(), $conn->insert_id]);
            }
            else
                throw new \Exception('Művelethiba (else)');


        }
            break;

        case 'torles':
        {
            $torlendoID = null;
            if (isset($_REQUEST['ertekeles_id']) && $_REQUEST['ertekeles_id'] != '')
                $torlendoID = $_REQUEST['ertekeles_id'];

            if ($torlendoID == null)
                throw new \Exception('Hiányzó paraméter. Nincs megadva az értékelés ID!');

            if (!AktualisProfilIrtaEztAzErtekelest_sec_checks($conn, $torlendoID))
                throw new \Exception('Valami nem stimmel az értékeléssel.');

            $stmt = $conn->prepare("DELETE FROM ertekelesek WHERE id = ?;");
            $stmt->bind_param('i', $torlendoID);

            if (!$stmt->execute())
                throw new \Exception('$stmt->execute() is false: m4 ' . $stmt->error);

            $logger->info('Értékelés törölve', [(isset($_SESSION['profilint_id'])) ? $_SESSION['profilint_id'] : 'No Internal ID', \Eszkozok\Eszk::get_client_ip_address(), $torlendoID]);

        }
            break;

        default:
            throw new \Exception('Hibás paraméter: művelet.');
            break;
    }


    try
    {
        $conn->close();
    }
    catch (\Exception $exc)
    {
    }

    ob_clean();
    die('siker1234');
}
catch (\Exception $e)
{
    try
    {
        $conn->close();
    }
    catch (\Exception $exc)
    {
    }

    ob_clean();
    //Eszkozok\Eszk::dieToErrorPage('2085: ' . $e->getMessage());
    echo $e->getMessage();
}