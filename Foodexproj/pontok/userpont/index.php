<?php
session_start();

require_once __DIR__ . '/../../Eszkozok/Eszk.php';
require_once __DIR__ . '/../../Eszkozok/param.php';
require_once __DIR__ . '/../../profil/Profil.php';

if (!isset($_SESSION['profilint_id']))
    Eszkozok\Eszk::RedirectUnderRoot('');

if (!IsParamSet('int_id'))
    Eszkozok\Eszk::RedirectUnderRoot('pontok');

$MegjelenitettProfil = \Eszkozok\Eszk::GetTaroltProfilAdat(GetParam('int_id'));


//var_dump($MegjelenitettProfil);

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Fx - Pontok</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <!--    <link rel="stylesheet" href="../backgradient.css">-->

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="main.css">

    <link rel="stylesheet" href="modal.css">


    <script src='https://www.google.com/recaptcha/api.js'></script>
</head>

<body style="background: #151515">

<a href=".." style="font-size: larger; text-decoration: none;color: yellow"> << Pontok</a>

<div style="width: 99vw; text-align: center">
    <h1 style="horiz-align: center; color: gold; margin-bottom: 0;"><?php echo $MegjelenitettProfil->getNev(); ?></h1>

    <h1 style="horiz-align: center; color: gray; margin: 0">Pontjai</h1>
</div>


<div id="osszhastablazat" class="tablaDiv" style="margin-top: 1.5%;">

    <table class="tabla">

        <colgroup>
            <col span="1" style="width: 10%;">
            <col span="1" style="width: 8%;">
            <col span="1" style="width: 4%;">
            <col span="1" style="width: 4%;">
            <col span="1" style="width: 54%">
        </colgroup>

        <tr class="tablaSor">
            <td class="tablaCella tablaElsosor">
                <p>Műszak <span style="font-weight: normal; font-size: small">(ID)</span></p>
            </td>
            <td class="tablaCella tablaElsosor">
                <p>Idő</p>
            </td>
            <td class="tablaCella tablaElsosor">
                <p>Pont</p>
            </td>
            <td class="tablaCella tablaElsosor">
                <p>Fő</p>
            </td>
            <td class="tablaCella tablaElsosor">
                <p>Létrehozta</p>
            </td>

        </tr>

        <?php
        try
        {
            $conn = Eszkozok\Eszk::initMySqliObject();

            if (!$conn)
                throw new \Exception('SQL hiba: $conn is \'false\'');


            $MuszakLetszamok = array();//Cacheli az muszid - Létszám párokat a műszakok közül, hogy ne kelljen minden műszaknál új lekérdezés a létszámért
            $MuszakKiirokNevei = array();//Cacheli az internal_id - Név párokat a kiírók közül, hogy ne kelljen minden műszaknál új lekérdezés a névért


            $stmt = $conn->prepare("SELECT `muszid` FROM `fxjelentk` WHERE `jelentkezo` = ? AND status = 1;");
            if (!$stmt)
                throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

            $buffInt = $MegjelenitettProfil->getInternalID();
            $stmt->bind_param('s', $buffInt);

            if ($stmt->execute())
            {
                $resultJelentk = $stmt->get_result();
                if ($resultJelentk->num_rows > 0)
                {
                    $jelMuszakIDk = array();

                    while ($rowJelentk = $resultJelentk->fetch_assoc())
                    {
                        $jelMuszakIDk[] = $conn->escape_string($rowJelentk['muszid']);
                    }

                    //var_dump($muszakIDk);

                    $vittMuszakIDk = array();

                    foreach ($jelMuszakIDk as $muszidakt)
                    {
                        if (!array_key_exists($muszidakt, $MuszakLetszamok))
                            $MuszakLetszamok[$muszidakt] = Eszkozok\Eszk::GetTaroltMuszakAdatWithConn($muszidakt, $conn)->letszam;


                        $stmt = $conn->prepare("SELECT * FROM `fxjelentk` WHERE `muszid` = ? AND `status` = 1 ORDER BY `ID` ASC;");
                        if (!$stmt)
                            throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                        $stmt->bind_param('i', $muszidakt);

                        if ($stmt->execute())
                        {
                            $resultKeret = $stmt->get_result();
                            if ($resultKeret->num_rows > 0)
                            {

                                for ($i = 0; ($rowKeret = $resultKeret->fetch_assoc()) && $i < $MuszakLetszamok[$muszidakt]; ++$i)
                                {
                                    //echo $i . ' - ' . $muszidakt . '<br>';


                                    if ($MegjelenitettProfil->getInternalID() == $rowKeret['jelentkezo'])
                                    {
                                        $vittMuszakIDk[] = $muszidakt;
                                        break;
                                    }

                                   // var_dump($rowKeret);

                                }
                            }
                        }

                    }
                    if (count($vittMuszakIDk) > 0)
                    {
                        //`idoveg` < NOW() : Csak arra a műszakra kap pontot, ami már lezárult
                        //TODO: idoveg < now() - ból kivenni a TRUE-t
                        $stmt = $conn->prepare("SELECT * FROM `fxmuszakok` WHERE  (FALSE || `idoveg` < NOW()) AND `ID` IN (" . implode(',', $vittMuszakIDk) . ") ORDER BY `idokezd` DESC;");
                        if (!$stmt)
                            throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                        if ($stmt->execute())
                        {
                            $resultMuszak = $stmt->get_result();
                            if ($resultMuszak->num_rows > 0)
                            {
                                while ($rowMuszak = $resultMuszak->fetch_assoc())
                                {
                                    //var_dump($rowMuszak);


                                    $idokezd = DateTime::createFromFormat("Y-m-d H:i:s", $rowMuszak['idokezd']);

                                    $idostringbuff = $idokezd->format('Y');
                                    $idostringbuff .= ' ';
                                    $idostringbuff .= $idokezd->format('m-d');
                                    $idostringbuff .= '<br>';
                                    $idostringbuff .= $idokezd->format('H:i');

                                    $idostringbuff .= ' - ';

                                    $idoveg = DateTime::createFromFormat("Y-m-d H:i:s", $rowMuszak['idoveg']);
                                    $idostringbuff .= $idoveg->format('H:i');


                                    if (!array_key_exists($rowMuszak['kiirta'], $MuszakKiirokNevei))
                                        $MuszakKiirokNevei[$rowMuszak['kiirta']] = Eszkozok\Eszk::GetTaroltProfilAdat($rowMuszak['kiirta'])->getNev();


                                    ?>

                                    <tr class="tablaSor">
                                        <td class="tablaCella oszlopNev">
                                            <p><?php echo htmlspecialchars($rowMuszak['musznev']) . '  <span style="font-weight: normal; font-size: small">(' . htmlspecialchars($rowMuszak['ID']) . ')</span>'; ?></p>
                                        </td>
                                        <td class="tablaCella oszlopIdo">
                                            <p><?php echo $idostringbuff; ?></p>
                                        </td>
                                        <td class="tablaCella oszlopPont">
                                            <p><?php echo htmlspecialchars($rowMuszak['pont']) . ' pont'; ?></p>
                                        </td>
                                        <td class="tablaCella oszlopLetszam">
                                            <p><?php echo htmlspecialchars($rowMuszak['letszam']) . ' fő'; ?></p>
                                        </td>
                                        <td class="tablaCella oszlopKiirta">
                                            <p><?php echo htmlspecialchars($MuszakKiirokNevei[$rowMuszak['kiirta']]); ?></p>
                                        </td>

                                    </tr>
                                    <?php
                                }
                            }
                        }
                        else
                            throw new \Exception('$stmt->execute() 3 nem sikerült' . ' :' . $conn->error);
                    }
                }
            }
            else
                throw new \Exception('$stmt->execute() 2 nem sikerült' . ' :' . $conn->error);


        }
        catch (\Exception $e)
        {
            ob_clean();
            Eszkozok\Eszk::dieToErrorPage('3014: ' . $e->getMessage());
        }
        ?>

    </table>
</div>

</body>

</html>


