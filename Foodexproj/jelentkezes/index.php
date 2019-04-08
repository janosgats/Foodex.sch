<?php
session_start();

require_once '../Eszkozok/Eszk.php';
require_once '../Eszkozok/param.php';
require_once __DIR__ . '/../Eszkozok/MonologHelper.php';
include_once '../profil/Profil.php';
include_once 'jelentkez.php';

$logger = new \MonologHelper('jelentkezes/index.php');

\Eszkozok\Eszk::ValidateLogin();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();


doJelentkezes();

if($AktProfil->getUjMuszakJog() == 1)
{
    if(IsURLParamSet('muszakokaktival') && GetURLParam('muszakokaktival') == 1)
    {
        $conn;
        try
        {
            $conn = \Eszkozok\Eszk::initMySqliObject();

            if($conn->multi_query("SET @uids := -99;
UPDATE `fxmuszakok`
   SET aktiv = '1'
 WHERE aktiv <> '1'
   AND ( SELECT @uids := CONCAT_WS(',', id, @uids) );
SELECT @uids as modified_row_IDs;"))
            {
                while($conn->more_results())
                    $conn->next_result();

                $lastres = $conn->store_result();
                $row = $lastres->fetch_assoc();
                //var_dump($row);

                $modified_row_IDs = explode(",", $row['modified_row_IDs']);

                //var_dump($modified_row_IDs);

                foreach ($modified_row_IDs as $rowID)
                {
                    if((int)$rowID != -99)
                    {
                       // var_dump($rowID);
                    $logger->info('Műszak lett aktiválva! MUSZAKTIVAL', [(isset($_SESSION['profilint_id'])) ? $_SESSION['profilint_id'] : 'No Internal ID', \Eszkozok\Eszk::get_client_ip_address(), (int)$rowID]);
                    $logger->info('MUSZAKTIVAL', [(int)$rowID]);
                    }
                }

            }
            else
                throw new Exception('Error at $conn->multi_query()');
        }
        catch(\Exception $e)
        {
            \Eszkozok\Eszk::dieToErrorPage('76734: ' . $e->getMessage());
        }
        finally
        {
            try
            {
                $conn->close();
            }
            catch(Exception $e){}
        }
    }
}

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
    <title>Fx - Jelentkezés Műszakra</title>

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
            <col span="1" style="width: 10%;">
            <col span="1" style="width: 2%;">
            <col span="1" style="width: 8%;">
            <col span="1" style="width: 4%;">
            <col span="1" style="width: 56%;">
            <?php
            if ($AktProfil->getUjMuszakJog() == 1)
            {
                ?>

                <col span="1" style="width: 2%;">
                <?php
            }
            ?>

        </colgroup>

        <?php

        if($AktProfil->getUjMuszakJog() == 1)
        {
            ?>
                <form method="POST" action="" id="hiddenmuszakokaktivalpostform" hidden>
                    <input name="muszakokaktival" value="1" hidden/>
                </form>

            <a href="#" onclick="var r = confirm('Biztosan aktiválod az összes inaktív műszakot?\nEz a művelet nem visszavonható!'); if(r) document.getElementById('hiddenmuszakokaktivalpostform').submit();" style="font-size: larger; color: yellow"> Az összes inaktív műszak aktiválása most!</a>
                <br><br>
            <?php
        }



        $OsszesMuszakMutat = false;

        try
        {
            if (IsURLParamSet('osszmusz') && GetURLParam('osszmusz') == 1)
            {
                $OsszesMuszakMutat = true;
                ?>
                <a href="?osszmusz=0" style="font-size: larger; color: greenyellow"> Csak az aktuális műszakokat
                    mutasd!</a>
                <br><br>
                <?php
            }
            else
            {
                ?>
                <a href="?osszmusz=1" style="font-size: larger; color: greenyellow"> Mutasd az összes műszakot!</a>
                <br><br>
                <?php
            }
        }
        catch (\Exception $e)
        {
        }

        try
        {
            $conn = Eszkozok\Eszk::initMySqliObject();


            if (!$conn)
                throw new \Exception('SQL hiba: $conn is \'false\'');

            ///`fxmuszakok` (`kiirta`, `musznev`, `idokezd`, `idoveg`, `letszam`, `pont`)

            if ($OsszesMuszakMutat)
                $stmt = $conn->prepare("SELECT * FROM `fxmuszakok` ORDER BY `idokezd` DESC;");
            else
                $stmt = $conn->prepare("SELECT * FROM `fxmuszakok` WHERE `idokezd` >= CURDATE() ORDER BY `idokezd` DESC;");

            if (!$stmt)
                throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);


            if ($stmt->execute())
            {
                $result = $stmt->get_result();

                if ($result->num_rows > 0)
                {
                    while ($row = $result->fetch_assoc())
                    {
                        if($AktProfil->getUjMuszakJog() != 1 && $row['aktiv'] != 1)
                            continue;

                        //var_dump($row);
                        $kiiroProfil = Eszkozok\Eszk::GetTaroltProfilAdat($row['kiirta']);

                        $idokezd = DateTime::createFromFormat("Y-m-d H:i:s", $row['idokezd']);

                        $idostringbuff = \Eszkozok\Eszk::getNameOfDayOfWeek(date('N', $idokezd->getTimestamp()), true);
                        $idostringbuff .= '<br>';
                        $idostringbuff .= $idokezd->format('H:i');

                        $idostringbuff .= ' - ';

                        $idoveg = DateTime::createFromFormat("Y-m-d H:i:s", $row['idoveg']);
                        $idostringbuff .= $idoveg->format('H:i');

                        $jelentkIdoszakVan = 1;

                        if (date("Y-m-d H:i:s") > $idokezd->format('Y-m-d H:i:s'))
                            $jelentkIdoszakVan = 0;

                        $jelintidtomb = \Eszkozok\Eszk::getJelentkezokListajaWithConn($row['ID'], $conn);

                        if (in_array($_SESSION['profilint_id'], $jelintidtomb))
                            $felvetel = 0;
                        else
                            $felvetel = 1;

                        $jelnevtomb = \Eszkozok\Eszk::getColumnAdatTombFromInternalIdTombWithConn($jelintidtomb, 'nev', $conn);


                        $jelnevstring = '';

                        for ($i = 0; $i < count($jelnevtomb);)
                        {
                            $jelnevstring .= '<a style="cursor: pointer;text-decoration: none; color: inherited" href="../profil/?mprof=' . $jelintidtomb[$i] . '" >';


                            if ($i < $row['letszam'])
                                $jelnevstring .= '<p class="varolistaElso">';
                            else
                                $jelnevstring .= '<p class="varolistaLemaradt">';


                            $jelnevstring .= htmlspecialchars($jelnevtomb[$i]);


                            $jelnevstring .= '</p>';
                            $jelnevstring .= '</a>';

                            ++$i;

                            if ($i < count($jelnevtomb))
                                $jelnevstring .= ', ';
                        }
                        ?>

                        <!--                        ShowModal(id,kiirta, musznev, idokezd, idoveg, letszam, pont, mospont, jelaktiv)-->

                        <tr class="tablaSor">
                            <td class="tablaCella oszlopNev">
                                <p style="<?php if($row['aktiv'] != 1) echo 'color:red'; ?>" onclick="ShowModal('<?php echo $row['ID']; ?>','<?php echo htmlspecialchars($kiiroProfil->getNev()); ?>', '<?php echo $row['musznev']; ?>', '<?php echo $idokezd->format('Y-m-d     H:i'); ?>', '<?php echo $idoveg->format('Y-m-d     H:i'); ?>', '<?php echo htmlspecialchars($row['letszam']); ?>', '<?php echo htmlspecialchars($row['pont']); ?>','<?php echo htmlspecialchars($row['mospont']); ?>', '<?php echo htmlspecialchars($row['megj']); ?>', '<?php echo $jelentkIdoszakVan; ?>', '<?php echo $felvetel; ?>');"><?php echo htmlspecialchars($row['musznev']); ?></p>
                            </td>
                            <td class="tablaCella oszlopReszletek">
                                <p onclick="ShowModal('<?php echo $row['ID']; ?>','<?php echo htmlspecialchars($kiiroProfil->getNev()); ?>', '<?php echo $row['musznev']; ?>', '<?php echo $idokezd->format('Y-m-d     H:i'); ?>', '<?php echo $idoveg->format('Y-m-d     H:i'); ?>', '<?php echo htmlspecialchars($row['letszam']); ?>', '<?php echo htmlspecialchars($row['pont']); ?>','<?php echo htmlspecialchars($row['mospont']); ?>', '<?php echo htmlspecialchars($row['megj']); ?>', '<?php echo $jelentkIdoszakVan; ?>', '<?php echo $felvetel; ?>');">
                                    <i
                                        class="fa fa-plus-square-o fa-2x"></i></p>
                            </td>
                            <td class="tablaCella oszlopPont">
                                <p><?php echo $idostringbuff; ?></p>
                            </td>
                            <td class="tablaCella oszlopLetszam">
                                <p><?php echo htmlspecialchars($row['letszam']); ?> fő</p>
                            </td>
                            <td class="tablaCella oszlopVarolista">
                                <?php echo $jelnevstring; ?>
                            </td>

                            <?php
                            if ($AktProfil->getUjMuszakJog() == 1)
                            {
                                ?>
                                <td class="tablaCella oszlopReszletek">
                                    <p>
                                        <a href="../muszedit?muszid=<?php echo $row['ID']; ?>" target="_blank"
                                           style="text-decoration: none; color: inherit">
                                            <i class="fa fa-cog fa-2x"></i>
                                        </a>
                                    </p>
                                </td>
                                <?php
                            }
                            ?>

                        </tr>
                        <?php

                    }


                }

            }
            else
            {
                throw new \Exception('Az SQL parancs végrehajtása nem sikerült.' . ' :' . $conn->error);
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

<!-- The Modal -->
<div id="myModal" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
        <div class="modal-header">
            <span class="close">&times;</span>

            <h2 id="modalheadertext">Jelentkezés</h2>
        </div>
        <div class="modal-body" id=modalbody">
            <p id="modalkiirta">Kiírta: </p>

            <div style="display: inline-block; margin: 0; padding: 0; text-align: justify">
                <p id="modalidokezd" style="white-space:pre;margin: 0; padding: 0">Kezdet: </p>

                <div
                    style="display: flex; width: 100%;justify-content: space-between;text-align: justify; margin: 0; padding: 0;">

                    <p id="modalidovegSZOVEG" style="margin-bottom: 0; padding-bottom: 0; ;display: inline">Vég: </p>

                    <p id="modalidovegERTEK"
                       style="white-space:pre;margin-bottom: 0; padding-bottom: 0;display: inline"></p>

                </div>
            </div>

            <p id="modalletszam">Maximális létszám: </p>

            <p id="modalpont">Közösségi pont: </p>

            <p id="modalmospont">Pont mosogatásért: </p>

            <p id="modalmegj">Wukker intelmei: </p>

        </div>
        <div class="modal-footer">
            <div id="jelentkezgombdiv" style="text-align: center;">

                <form action="" method="post">
                    <div>
                        <div class="g-recaptcha" style=" margin: 0 auto;display: inline-block;"
                             data-callback="greDataCallback"
                             data-sitekey="6LfTxl8UAAAAAO05DCRMYxdnDnRHd5E-uzN-J8fs"></div>
                    </div>
                    <br>
                    <input name="muszid" id="modalmuszakid" style="display: none">
                    <input name="muszmuv" value="felvetel" id="modalmuvelet" style="display: none">

                    <div class="tooltip" id="modalbtntooltip">
                        <span class="tooltiptext" id="modalbtntooltiptext">Oldd meg a reCaptcha-t!</span>
                        <button type="submit" class="popupbutton" id="modalsubmitbtn" disabled>Viszem!</button>
                    </div>
                </form>
            </div>
            <br>
        </div>
    </div>
</div>

<script>
    function greDataCallback()
    {
        document.getElementById('modalsubmitbtn').removeAttribute('disabled');
        document.getElementById('modalbtntooltip').classList.remove('tooltip');
        document.getElementById('modalbtntooltiptext').style.display = 'none';
    }
</script>

<script>
    // Get the modal
    var modal = document.getElementById('myModal');

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal

    function ShowModal(id, kiirta, musznev, idokezd, idoveg, letszam, pont, mospont, megj, jelaktiv, felvetel)
    {
        document.getElementById('modalheadertext').innerHTML = musznev + ' Jelentkezés';
        document.getElementById('modalkiirta').innerHTML = 'Kiírta: ' + kiirta;
        document.getElementById('modalidokezd').innerHTML = 'Kezdet: ' + idokezd;
        document.getElementById('modalidovegERTEK').innerHTML = idoveg;
        document.getElementById('modalletszam').innerHTML = 'Maximális létszám: ' + letszam;
        document.getElementById('modalpont').innerHTML = 'Közösségi pont: ' + pont;
        document.getElementById('modalmospont').innerHTML = 'Pont mosogatásért: ' + mospont;
        document.getElementById('modalmegj').innerHTML = 'Wukker intelmei: ' + megj;
        document.getElementById('modalmuszakid').value = id;

        if (felvetel == 0)
        {
            document.getElementById('modalmuvelet').value = 'lead';
            document.getElementById('modalsubmitbtn').innerHTML = 'Leadom!';
        }
        else
        {
            document.getElementById('modalmuvelet').value = 'felvesz';
            document.getElementById('modalsubmitbtn').innerHTML = 'Viszem!';
        }

        if (jelaktiv == 0)
        {
            document.getElementById('jelentkezgombdiv').style.display = 'none';
        }
        else
        {
            document.getElementById('jelentkezgombdiv').style.display = 'block';
        }
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function ()
    {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event)
    {
        if (event.target == modal)
        {
            modal.style.display = "none";
        }
    }
</script>

</body>

</html>
