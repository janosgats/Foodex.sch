<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';

\Eszkozok\Eszk::ValidateLogin();
$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();


?>

<!DOCTYPE html>
<html>

<head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-137789203-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-137789203-1');
    </script>

    <meta charset="UTF-8">
    <title>Fx - Pontok</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">


    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
</head>

<body style="background: #de520d">
<div class="container">

    <?php
    NavBar::echonavbar($AktProfil, 'pontok');
    ?>

    <div class="panel panel-default">
        <div class="panel-heading">Ponttáblázat</div>
        <div class="panel-body">
            <table class="table table-hover">
                <?php
                try
                {
                    \Eszkozok\Eszk::GetGlobalSettings(["pontozasi_idoszak_kezdete", "pontozasi_idoszak_vege"]);

                    $conn = Eszkozok\Eszk::initMySqliObject();

                    if (!$conn)
                        throw new \Exception('SQL hiba: $conn is \'false\'');


                    $MuszakLetszamok = array();//Cacheli az muszid - Létszám párokat a műszakok közül, hogy ne kelljen minden műszaknál új lekérdezés a létszámért


                    $stmt = $conn->prepare("SELECT `internal_id`,`nev` FROM `fxaccok` ORDER BY `nev` ASC;");
                    if (!$stmt)
                        throw new \Exception('SQL hiba: $stmt 2 is \'false\'' . ' :' . $conn->error);


                    if ($stmt->execute())
                    {
                        $resultAcc = $stmt->get_result();

                        if ($resultAcc->num_rows > 0)
                        {
                            while ($rowAcc = $resultAcc->fetch_assoc())
                            {
                                $pontszam = 0;

                                $stmt = $conn->prepare("SELECT `muszid`, `mosogat` FROM `fxjelentk` WHERE `jelentkezo` = ? AND status = 1;");
                                if (!$stmt)
                                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                                $stmt->bind_param('s', $rowAcc['internal_id']);

                                if ($stmt->execute())
                                {
                                    $resultJelentk = $stmt->get_result();
                                    if ($resultJelentk->num_rows > 0)
                                    {
                                        $jelMuszakIDk = array();//Jelentkezett műszakok ID-i a rowAcc-hoz
                                        $jelMosogatasok = array();

                                        while ($rowJelentk = $resultJelentk->fetch_assoc())
                                        {
                                            $aktmuszidBuff = $conn->escape_string($rowJelentk['muszid']);
                                            $jelMuszakIDk[] = $aktmuszidBuff;
                                            $jelMosogatasok[$aktmuszidBuff] = $conn->escape_string($rowJelentk['mosogat']);
                                        }

                                        // var_dump($jelMuszakIDk);

                                        $vittMuszakIDk = array();
                                        $vittMosogatasok = array();

                                        foreach ($jelMuszakIDk as $muszidakt)
                                        {
                                            if (!array_key_exists($muszidakt, $MuszakLetszamok))
                                            {
                                                $buff = Eszkozok\Eszk::GetTaroltMuszakAdatWithConn($muszidakt, false, $conn);
                                                if ($buff != false)
                                                    $MuszakLetszamok[$muszidakt] = $buff->letszam;
                                            }


                                            $stmt = $conn->prepare("SELECT * FROM `fxjelentk` WHERE `muszid` = ? AND `status` = 1 ORDER BY `ID` ASC;");
                                            if (!$stmt)
                                                throw new \Exception('SQL hiba: $stmt 5 is \'false\'' . ' :' . $conn->error);

                                            $stmt->bind_param('i', $muszidakt);

                                            if ($stmt->execute())
                                            {
                                                $resultKeret = $stmt->get_result();
                                                if ($resultKeret->num_rows > 0)
                                                {

                                                    for ($i = 0; ($rowKeret = $resultKeret->fetch_assoc()) && isset($MuszakLetszamok[$muszidakt]) && $i < $MuszakLetszamok[$muszidakt]; ++$i)
                                                    {
                                                        //echo $i . ' - ' . $muszidakt . '<br>';


                                                        if ($rowAcc['internal_id'] == $rowKeret['jelentkezo'])
                                                        {
                                                            $vittMuszakIDk[] = $muszidakt;
                                                            if ($jelMosogatasok[$muszidakt] == 1)//Ha az aktuálisan vitt műszakban mosogatott
                                                                $vittMosogatasok[] = $muszidakt;
                                                            break;
                                                        }

                                                        // var_dump($rowKeret);

                                                    }
                                                }
                                            }
                                            else
                                                throw new \Exception('$stmt->execute() 5 nem sikerült' . ' :' . $conn->error);
                                        }

                                        if (count($vittMuszakIDk) > 0)
                                        {
                                            //`idoveg` < NOW() : Csak arra a műszakra kap pontot, ami már lezárult
                                            $stmt = $conn->prepare("SELECT SUM(`pont`) AS OsszPontszam FROM `fxmuszakok` WHERE (FALSE || `idoveg` < NOW()) AND ( `idokezd` BETWEEN '" . $GLOBALS['pontozasi_idoszak_kezdete'] . "' AND '" . $GLOBALS['pontozasi_idoszak_vege'] . "' ) AND `ID` IN (" . implode(',', $vittMuszakIDk) . ");");
                                            if (!$stmt)
                                                throw new \Exception('SQL hiba: $stmt 3 is \'false\'' . ' :' . $conn->error);

                                            if ($stmt->execute())
                                            {
                                                $resultMuszak = $stmt->get_result();
                                                if ($resultMuszak->num_rows == 1)
                                                {
                                                    $rowMuszak = $resultMuszak->fetch_assoc();
                                                    $pontszam += $rowMuszak['OsszPontszam'];
                                                }
                                                if (count($vittMosogatasok) > 0)
                                                {
                                                    $stmt = $conn->prepare("SELECT SUM(`mospont`) AS OsszPontszam FROM `fxmuszakok` WHERE (FALSE || `idoveg` < NOW()) AND ( `idokezd` BETWEEN '" . $GLOBALS['pontozasi_idoszak_kezdete'] . "' AND '" . $GLOBALS['pontozasi_idoszak_vege'] . "' ) AND `ID` IN (" . implode(',', $vittMosogatasok) . ");");
                                                    if (!$stmt)
                                                        throw new \Exception('SQL hiba: $stmt 4 is \'false\'' . ' :' . $conn->error);

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
                                                        throw new \Exception('$stmt->execute() 4 nem sikerült' . ' :' . $conn->error);
                                                }
                                            }
                                            else
                                                throw new \Exception('$stmt->execute() 3 nem sikerült' . ' :' . $conn->error);
                                        }
                                    }
                                }
                                else
                                    throw new \Exception('$stmt->execute() 2 nem sikerült' . ' :' . $conn->error);
                                $pontszam = round($pontszam + +\Eszkozok\Eszk::GetAccKompenzaltPontokWithConn($rowAcc['internal_id'], $conn), 1);
                                ?>

                                <tr>
                                    <td>

                                        <a style="cursor: pointer" href="<?php echo '../profil/?mprof=' . $rowAcc['internal_id']; ?>"><p><?php echo htmlspecialchars($rowAcc['nev']); ?></p></a>
                                    </td>
                                    <td>
                                        <a class="badge"  href="userpont/?int_id=<?php echo $rowAcc['internal_id']; ?>"><?php echo htmlspecialchars($pontszam) . ' pont'; ?></a>
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
    </div>
</div>

</body>
</html>


