<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';


if (!isset($_SESSION['profilint_id']))
    Eszkozok\Eszk::RedirectUnderRoot('');

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

<a href="../profil" style="font-size: larger; text-decoration: none;color: yellow"> << Profil</a>

<div id="osszhastablazat" class="tablaDiv" style="margin-top: 1.5%;">

    <table class="tabla">

        <colgroup>
            <col span="1" style="width: 20%;">
            <col span="1" style="width: 5%;">
            <!--            <col span="1" style="width: 8%;">-->
        </colgroup>

        <?php
        try
        {

            $conn = Eszkozok\Eszk::initMySqliObject();

            if (!$conn)
                throw new \Exception('SQL hiba: $conn is \'false\'');


            $MuszakLetszamok = array();//Cacheli az muszid - Létszám párokat a műszakok közül, hogy ne kelljen minden műszaknál új lekérdezés a létszámért


            $stmt = $conn->prepare("SELECT `internal_id`,`nev` FROM `fxaccok` ORDER BY `nev` ASC;");
            if (!$stmt)
                throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);


            if ($stmt->execute())
            {
                $resultAcc = $stmt->get_result();

                if ($resultAcc->num_rows > 0)
                {
                    while ($rowAcc = $resultAcc->fetch_assoc())
                    {
                        $pontszam = 0;

                        $stmt = $conn->prepare("SELECT `muszid` FROM `fxjelentk` WHERE `jelentkezo` = ? AND status = 1;");
                        if (!$stmt)
                            throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                        $stmt->bind_param('s', $rowAcc['internal_id']);

                        if ($stmt->execute())
                        {
                            $resultJelentk = $stmt->get_result();
                            if ($resultJelentk->num_rows > 0)
                            {
                                $jelMuszakIDk = array();//Jelentkezett műszakok ID-i a rowAcc-hoz

                                while ($rowJelentk = $resultJelentk->fetch_assoc())
                                {
                                    $jelMuszakIDk[] = $conn->escape_string($rowJelentk['muszid']);
                                }

                               // var_dump($jelMuszakIDk);

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


                                                if ($rowAcc['internal_id'] == $rowKeret['jelentkezo'])
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
                                    $stmt = $conn->prepare("SELECT SUM(`pont`) AS OsszPontszam FROM `fxmuszakok` WHERE (FALSE || `idoveg` < NOW()) AND `ID` IN (" . implode(',', $vittMuszakIDk) . ");");
                                    if (!$stmt)
                                        throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                                    if ($stmt->execute())
                                    {
                                        $resultMuszak = $stmt->get_result();
                                        if ($resultMuszak->num_rows == 1)
                                        {
                                            $rowMuszak = $resultMuszak->fetch_assoc();
                                            $pontszam += $rowMuszak['OsszPontszam'];
                                        }
                                    }
                                    else
                                        throw new \Exception('$stmt->execute() 3 nem sikerült' . ' :' . $conn->error);
                                }

                            }
                        }
                        else
                            throw new \Exception('$stmt->execute() 2 nem sikerült' . ' :' . $conn->error);

                        ?>

                        <tr class="tablaSor">
                            <td class="tablaCella oszlopNev">
                                <p><?php echo htmlspecialchars($rowAcc['nev']); ?></p>
                            </td>
                            <td class="tablaCella oszlopReszletek">
                                <a href="userpont/?int_id=<?php echo $rowAcc['internal_id']; ?>"
                                      style="text-decoration: none; color: inherit"><p>
                                        <i class="fa fa-plus-square-o fa-2x"></i>
                                    </p></a>
                            </td>
                            <td class="tablaCella oszlopPont">
                                <p><?php echo htmlspecialchars($pontszam) . ' pont'; ?></p>
                            </td>

                        </tr>
                        <?php

                    }


                }

            }
            else
            {
                throw new \Exception('$stmt->execute() 1 nem sikerült' . ' :' . $conn->error);
            }

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


