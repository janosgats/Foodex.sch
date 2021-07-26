<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once __DIR__ . '/../Eszkozok/param.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';
require_once __DIR__ . '/../Eszkozok/MonologHelper.php';
require_once __DIR__ . '/../Eszkozok/entitas/Profil.php';
require_once __DIR__ . '/../3rdparty/securimage/securimage.php';
require_once 'jelentkez.php';

$logger = new \MonologHelper('jelentkezes/index.php');

\Eszkozok\LoginValidator::MuszJelJog_DiesToErrorrPage();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();


doJelentkezes();


if (\Eszkozok\LoginValidator::AdminJog_NOEXIT()) {
    if (IsURLParamSet('muszakokaktival') && GetURLParam('muszakokaktival') == 1) {
        $conn;
        try {
            $conn = \Eszkozok\Eszk::initMySqliObject();

            $IDsToActivate = [];

            if(!$conn->query("SELECT ID
                                FROM `fxmuszakok` 
                                WHERE aktiv <> '1'")){
                throw new Exception('Error at $conn->query()');
            }

            $IDsToActivate[] = intval($conn->store_result()->fetch_assoc()['ID']);
            while ($conn->more_results()) {
                $conn->next_result();
                $IDsToActivate[] = intval($conn->store_result()->fetch_assoc()['ID']);
            }

            $implodedIdsToActivate = implode(",", $IDsToActivate);

            if ($conn->query("UPDATE `fxmuszakok`
                                       SET aktiv = '1'
                                     WHERE ID in ({$implodedIdsToActivate})")
            ) {
                foreach ($IDsToActivate as $rowID) {
                    if ((int)$rowID != -99) {
                        $logger->info('Műszak lett aktiválva! MUSZAKTIVAL', [(isset($_SESSION['profilint_id'])) ? $_SESSION['profilint_id'] : 'No Internal ID', \Eszkozok\Eszk::get_client_ip_address(), (int)$rowID]);
                        $logger->info('MUSZAKTIVAL', [(int)$rowID]);
                    }
                }

            } else
                throw new Exception('Error at $conn->multi_query()');
        }
        catch (\Exception $e) {
            \Eszkozok\Eszk::dieToErrorPage('76734: ' . $e->getMessage());
        }
        finally {
            try {
                $conn->close();
            }
            catch (Exception $e) {
            }
        }
    }
}

$IsSecurimageBypassed = false;
$IsSecurimageCorrect = false;
$image = new Securimage();
if (isset($_POST['securimage_captcha_code']) && $image->check($_POST['securimage_captcha_code']) == true)//Captcha
{
    $IsSecurimageCorrect = true;
}

if (\Eszkozok\LoginValidator::AdminJog_NOEXIT()) {
    $IsSecurimageCorrect = true;
    $IsSecurimageBypassed = true;
}

try {
    /*
    FEATURE:   Ha elég idő telt el műszakkiírás után, akkor az oldal már semmiképp sem mutatná a captcha-t. (golbal_settings table-ből az értéket hozzáadná a legútóbbi kiírás idejéhez.)
    PROBLÉMA:  Ha kiírnak egy műszakot és e-miatt hirtelen megjelenik a captcha, abból egy crawling bot tudni forga, hogy új műszak lett kiírva, így küldhet a "hackernek" értesítést.
    KONKLÚZIÓ: Még NE implementáld, amíg NINCS a problémára megoldás!
    */
}
catch (\Exception $e) {
}

?>


<!DOCTYPE html>
<html>

<head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-137789203-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag()
        {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-137789203-1');
    </script>

    <meta charset="UTF-8">
    <title>Fx - Jelentkezés Műszakra</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <!--    <link rel="stylesheet" href="../backgradient.css">-->

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="../3rdparty/bootstrap-iso.css">

    <link rel="stylesheet" href="main.css">

    <link rel="stylesheet" href="modal.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
</head>

<body style="background: #151515; margin-top: 0;">

<div class="bootstrap-iso" style="background: #151515;">
    <div class="container">
        <?php
        NavBar::echonavbar('jelentkezes');
        ?>

        <?php

        if ($IsSecurimageBypassed) {
            ?>
            <div style="text-align: center; width: 100%; margin-top: -10px">
                <p>Admin jogaid miatt a CAPTCHA-t számodra kikapcsoltuk.</p>
            </div>
            <?php
        }

        if ($IsSecurimageCorrect) {
            if (\Eszkozok\LoginValidator::AdminJog_NOEXIT()) {
                ?>
                <form method="POST" action="" id="hiddenmuszakokaktivalpostform" hidden>
                    <input name="muszakokaktival" value="1" hidden/>
                </form>

                <button class="btn btn-warning pull-left" onclick="var r = confirm('Biztosan aktiválod az összes inaktív műszakot?\nEz a művelet nem visszavonható!'); if(r) document.getElementById('hiddenmuszakokaktivalpostform').submit();"
                        type="button">Összes inaktív műszak aktiválása!
                </button>

                <br><br>
                <?php
            }


            $OsszesMuszakMutat = false;

            try {
                if (IsURLParamSet('osszmusz') && GetURLParam('osszmusz') == 1) {
                    $OsszesMuszakMutat = true;
                    ?>


                    <a class="btn btn-primary pull-left" href="?osszmusz=0" type="button">Csak az aktuális műszakokat mutasd!</a>
                    <br><br>
                    <?php
                } else {
                    ?>
                    <a class="btn btn-primary pull-left" href="?osszmusz=1" type="button">Mutasd az összes műszakot!</a>
                    <br><br>
                    <?php
                }
            }
            catch (\Exception $e) {
            }
        }
        ?>


    </div>
</div>

<?php
if ($IsSecurimageCorrect) {
    ?>
    <div id="osszhastablazat" class="tablaDiv" style="margin-top: 1.5%;">

        <table class="tabla">

            <colgroup>
                <col span="1" style="width: 12%;">
                <col span="1" style="width: 8%;">
                <col span="1" style="width: 4%;">
                <col span="1" style="width: 56%;">
                <?php
                if (\Eszkozok\LoginValidator::AdminJog_NOEXIT()) {
                    ?>

                    <col span="1" style="width: 2%;">
                    <?php
                }
                ?>

            </colgroup>

            <?php


            try {
                $conn = Eszkozok\Eszk::initMySqliObject();

                $AktUserJelentkezesDelayInSeconds = \Eszkozok\Eszk::GetJelDelayTimeByPontWithConn(\Eszkozok\Eszk::GetAccKompenzaltPontokWithConn($_SESSION['profilint_id'], $conn), $conn);
                $DoesAktUserHaveAktivJelentkezes = DoesAktUserHaveAktivJelentkezes($conn);


                if ($OsszesMuszakMutat)
                    $stmt = $conn->prepare("SELECT `fxmuszakok`.*, `korok`.`nev` AS KorNev, `logs`.`datetime` AS aktivalas_ideje  FROM `fxmuszakok` LEFT JOIN `korok` ON `korok`.`id` = `fxmuszakok`.`korid` LEFT JOIN `logs` ON `logs`.`message` = 'MUSZAKTIVAL' AND CONCAT('[', `fxmuszakok`.`id`, ']') = `logs`.`context` ORDER BY `idokezd` DESC;");
                else
                    $stmt = $conn->prepare("SELECT `fxmuszakok`.*, `korok`.`nev` AS KorNev, `logs`.`datetime` AS aktivalas_ideje  FROM `fxmuszakok` LEFT JOIN `korok` ON `korok`.`id` = `fxmuszakok`.`korid` LEFT JOIN `logs` ON `logs`.`message` = 'MUSZAKTIVAL' AND CONCAT('[', `fxmuszakok`.`id`, ']') = `logs`.`context`  WHERE `idokezd` >= CURDATE() ORDER BY `idokezd` DESC;");

                if (!$stmt)
                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);


                if ($stmt->execute()) {
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            if (\Eszkozok\LoginValidator::AdminJog_NOEXIT() == false && $row['aktiv'] != 1)
                                continue;

                            //////////////////////////////////////////////////////////////


                            $AktMuszakFelvetelKezdete_AktUserSzamara_PontszamAlapjan = null;
                            if (isset($row['aktivalas_ideje'])) {
                                $AktMuszakFelvetelKezdete_AktUserSzamara_PontszamAlapjan = \DateTime::createFromFormat('Y-m-d H:i:s', $row['aktivalas_ideje']);
                                $AktMuszakFelvetelKezdete_AktUserSzamara_PontszamAlapjan->add(\DateInterval::createFromDateString($AktUserJelentkezesDelayInSeconds . ' seconds'));
                            } else {
                                $AktMuszakFelvetelKezdete_AktUserSzamara_PontszamAlapjan = DateTime::createFromFormat('Y-m-d H:i:s', '1998-10-01 00:00:00');
                            }


                            $AktMuszakFelvetelKezdete_AktUserSzamara_FelvettMuszakAlapjan = null;
                            if ($DoesAktUserHaveAktivJelentkezes) {
                                $AktMuszakFelvetelKezdete_AktUserSzamara_FelvettMuszakAlapjan = \DateTime::createFromFormat('Y-m-d H:i:s', $row['idokezd']);
                                $AktMuszakFelvetelKezdete_AktUserSzamara_FelvettMuszakAlapjan->sub(\DateInterval::createFromDateString(\Eszkozok\GlobalSettings::GetSetting('mas_muszakra_ennyivel_elotte_jelentkezhet') . ' seconds'));
                            } else {
                                $AktMuszakFelvetelKezdete_AktUserSzamara_FelvettMuszakAlapjan = DateTime::createFromFormat('Y-m-d H:i:s', '1998-10-01 00:00:00');
                            }

                            $AktMuszakFelvetelKezdete_AktUserSzamara_Vegleges = null;
                            $AktMuszakFelvetelKezdete_AktUserSzamara_Vegleges_Indoklas = null;
                            if ($AktMuszakFelvetelKezdete_AktUserSzamara_PontszamAlapjan > $AktMuszakFelvetelKezdete_AktUserSzamara_FelvettMuszakAlapjan) {
                                $AktMuszakFelvetelKezdete_AktUserSzamara_Vegleges = $AktMuszakFelvetelKezdete_AktUserSzamara_PontszamAlapjan;
                                $AktMuszakFelvetelKezdete_AktUserSzamara_Vegleges_Indoklas = 'pontszam';
                            } else {
                                $AktMuszakFelvetelKezdete_AktUserSzamara_Vegleges = $AktMuszakFelvetelKezdete_AktUserSzamara_FelvettMuszakAlapjan;
                                $AktMuszakFelvetelKezdete_AktUserSzamara_Vegleges_Indoklas = 'felvettmuszak';
                            }

                            //////////////////////////////////////////////////////////////

                            //var_dump($row);
                            $kiiroProfil = Eszkozok\Eszk::GetTaroltProfilAdat($row['kiirta'], true);

                            //////////////////////////////////////////////////////////////

                            $idokezd = DateTime::createFromFormat("Y-m-d H:i:s", $row['idokezd']);

                            $idostringbuff = \Eszkozok\Eszk::getNameOfDayOfWeek(date('N', $idokezd->getTimestamp()), true);
                            $idostringbuff .= '<br>';
                            $idostringbuff .= $idokezd->format('H:i');

                            $idostringbuff .= ' - ';

                            $idoveg = DateTime::createFromFormat("Y-m-d H:i:s", $row['idoveg']);
                            $idostringbuff .= $idoveg->format('H:i');

                            //////////////////////////////////////////////////////////////

                            $jelentkIdoszakVan = 1;

                            if (date("Y-m-d H:i:s") > $idokezd->format('Y-m-d H:i:s'))
                                $jelentkIdoszakVan = 0;

                            $jelentkezesAktiv = 0;

                            if ($jelentkIdoszakVan == 1 && $row['aktiv'] == 1)
                                $jelentkezesAktiv = 1;

                            $jelintidtomb = \Eszkozok\Eszk::getJelentkezokListajaWithConn($row['ID'], $conn);

                            if (in_array($_SESSION['profilint_id'], $jelintidtomb))
                                $felvetel = 0;
                            else
                                $felvetel = 1;

                            //////////////////////////////////////////////////////////////

                            $jelnevtomb = \Eszkozok\Eszk::getColumnAdatTombFromInternalIdTombWithConn($jelintidtomb, 'nev', $conn);


                            $jelnevstring = '';

                            for ($i = 0; $i < count($jelnevtomb);) {
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
                                <td class="tablaCella oszlopNev oszlopModalMegnyito">
                                    <p style="<?php if ($row['aktiv'] != 1) echo 'color:red'; ?>" onmouseover="setRowColor(this, 'yellow')" onmouseout="setRowColor(this, '<?php echo ($row['aktiv'] != 1) ? 'red' : 'white'; ?>')"
                                       onclick="ShowModal('<?php echo $row['ID']; ?>','<?php echo htmlspecialchars($kiiroProfil->getNev()); ?>', '<?php echo $row['musznev']; ?>', '<?php echo $idokezd->format('Y-m-d     H:i'); ?>', '<?php echo $idoveg->format('Y-m-d     H:i'); ?>', '<?php echo htmlspecialchars($row['letszam']); ?>', '<?php echo htmlspecialchars($row['pont']); ?>','<?php echo htmlspecialchars($row['mospont']); ?>', '<?php echo htmlspecialchars($row['KorNev']) ?: 'Nincs megadva'; ?>', '<?php echo htmlspecialchars($row['megj']); ?>', '<?php echo $jelentkezesAktiv; ?>',   '<?php echo $AktMuszakFelvetelKezdete_AktUserSzamara_Vegleges->format('Y-m-d H:i:s'); ?>',  '<?php echo $AktMuszakFelvetelKezdete_AktUserSzamara_Vegleges_Indoklas; ?>',  '<?php echo $felvetel; ?>');"><?php echo htmlspecialchars($row['musznev']); ?></p>
                                </td>

                                <td class="tablaCella oszlopPont oszlopModalMegnyito">
                                    <p style="<?php if ($row['aktiv'] != 1) echo 'color:red'; ?>" onmouseover="setRowColor(this, 'yellow')" onmouseout="setRowColor(this, '<?php echo ($row['aktiv'] != 1) ? 'red' : 'white'; ?>')"
                                       onclick="ShowModal('<?php echo $row['ID']; ?>','<?php echo htmlspecialchars($kiiroProfil->getNev()); ?>', '<?php echo $row['musznev']; ?>', '<?php echo $idokezd->format('Y-m-d     H:i'); ?>', '<?php echo $idoveg->format('Y-m-d     H:i'); ?>', '<?php echo htmlspecialchars($row['letszam']); ?>', '<?php echo htmlspecialchars($row['pont']); ?>','<?php echo htmlspecialchars($row['mospont']); ?>',  '<?php echo htmlspecialchars($row['KorNev']) ?: 'Nincs megadva'; ?>', '<?php echo htmlspecialchars($row['megj']); ?>', '<?php echo $jelentkezesAktiv; ?>',   '<?php echo $AktMuszakFelvetelKezdete_AktUserSzamara_Vegleges->format('Y-m-d H:i:s'); ?>',  '<?php echo $AktMuszakFelvetelKezdete_AktUserSzamara_Vegleges_Indoklas; ?>', '<?php echo $felvetel; ?>');"><?php echo $idostringbuff; ?></p>
                                </td>
                                <td class="tablaCella oszlopLetszam oszlopModalMegnyito">
                                    <p style="<?php if ($row['aktiv'] != 1) echo 'color:red'; ?>" onmouseover="setRowColor(this, 'yellow')" onmouseout="setRowColor(this, '<?php echo ($row['aktiv'] != 1) ? 'red' : 'white'; ?>')"
                                       onclick="ShowModal('<?php echo $row['ID']; ?>','<?php echo htmlspecialchars($kiiroProfil->getNev()); ?>', '<?php echo $row['musznev']; ?>', '<?php echo $idokezd->format('Y-m-d     H:i'); ?>', '<?php echo $idoveg->format('Y-m-d     H:i'); ?>', '<?php echo htmlspecialchars($row['letszam']); ?>', '<?php echo htmlspecialchars($row['pont']); ?>','<?php echo htmlspecialchars($row['mospont']); ?>',  '<?php echo htmlspecialchars($row['KorNev']) ?: 'Nincs megadva'; ?>', '<?php echo htmlspecialchars($row['megj']); ?>', '<?php echo $jelentkezesAktiv; ?>',  '<?php echo $AktMuszakFelvetelKezdete_AktUserSzamara_Vegleges->format('Y-m-d H:i:s'); ?>',  '<?php echo $AktMuszakFelvetelKezdete_AktUserSzamara_Vegleges_Indoklas; ?>', '<?php echo $felvetel; ?>');"><?php echo htmlspecialchars($row['letszam']); ?>
                                        fő</p>
                                </td>
                                <td class="tablaCella oszlopVarolista">
                                    <?php echo $jelnevstring; ?>
                                </td>

                                <?php
                                if (\Eszkozok\LoginValidator::AdminJog_NOEXIT()) {
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

                } else {
                    throw new \Exception('Az SQL parancs végrehajtása nem sikerült.' . ' :' . $conn->error);
                }

            }
            catch (\Exception $e) {
                ob_clean();
                Eszkozok\Eszk::dieToErrorPage('3014: ' . $e->getMessage());
            }
            ?>

        </table>
    </div>

    <script>
        function setRowColor(p, colorstyle)
        {
            var row = p.parentNode.parentNode;

            row.childNodes.forEach(function (node)
            {
                //console.log(node);

                if (node.nodeName == "TD" && node.classList.contains("oszlopModalMegnyito"))
                {
                    node.childNodes.forEach(function (node2)
                    {
                        if (node2.nodeName == "P")
                        {
                            node2.style.color = colorstyle;
                        }
                    });
                }
            });
        }
    </script>

    <?php
}//if($IsSecurimageCorrect) vége itt
?>

<?php
if (!$IsSecurimageCorrect) {
    ?>
    <div class="bootstrap-iso" style="text-align: center; width: 100%">

        <br>

        <div style="max-width: 300px; margin-left: auto; margin-right: auto;">
            <form method="post" autocomplete="off"> <!-- no 'action' parameter => submits to self page -->
                <div>
                    <?php
                    echo Securimage::getCaptchaHtml([], Securimage::HTML_IMG + Securimage::HTML_ICON_REFRESH + Securimage::HTML_AUDIO);
                    ?>
                </div>
                <div>
                    <input name="securimage_captcha_code" type="text" class="form-control" autofocus>
                    <br>
                    <button class="btn btn-success" type="submit">Lássuk a műszakokat!</button>
                </div>


                <div style="display: none">

                    <?php
                    //Az aktuális lekérés GET paramétereit beletesszük a captcha-val submitolando formba.
                    //Így azok továbbítódnak az oldal felé a captcha-val együtt, hogy aztán kiértékelődjenek, mint ha nem is lenne captcha.
                    foreach ($_GET as $key => $value) {
                        echo '<input name="' . $key . '" value="' . $value . '" >';
                    }

                    ?>

                </div>

            </form>
        </div>
        <br>
    </div>
    <?php
}
?>

<!-- The Modal -->
<div id="myModal" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
        <div class="modal-header">
            <span class="close">&times;</span>
            <a href="../muszedit" style="text-decoration: none" id="modalheaderlink"><h2 id="modalheadertext" style="color: white;text-decoration: none">Jelentkezés</h2></a>
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

            <p id="modalertekelokornev">Értékelő kör: </p>

            <p id="modalmegj">Wukker intelmei: </p>

        </div>
        <div class="modal-footer">
            <div id="visszaszamlalodiv" style="text-align: center;">
                <h2 id="visszaszamlalo_indok_header" style="color: #383838; font-family: 'Montserrat', sans-serif">A pontszámodból adódóan ennyi idő múlva jelentkezhetsz:</h2>

                <h1 id="visszaszamlalodiv_text" style="color: black; font-family: 'Montserrat', sans-serif"></h1>
            </div>
            <div id="jelentkezgombdiv" style="text-align: center;">

                <form action="" method="post">
                    <div>
                        <div class="g-recaptcha" style=" margin: 0 auto;display: inline-block;"
                             data-callback="greDataCallback"
                             data-sitekey="6LeGvaIUAAAAAKkBPMXKJsSKY1BRvq8HTWkeqIOh"></div>
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

    function GetTimeDifForModalVisszaszamlalo(d1, d2)
    {
        var dhours = Math.floor((d1.valueOf() - d2.valueOf()) / (3600 * 1000));
        var dmin = Math.floor((d1.valueOf() - d2.valueOf() - dhours * 3600 * 1000) / (60 * 1000));
        var dsec = Math.floor((d1.valueOf() - d2.valueOf() - dhours * 3600 * 1000 - dmin * 60 * 1000) / (1000));

        return dhours + ':' + dmin + ':' + dsec;
    }

    var RunningVisszaszamlaloID = -999;

    function JelntKezdeteVisszaszamolo(JelentkKezdeteDate, id)
    {
        if (RunningVisszaszamlaloID != id)
            return;//Ha már másik modalnyitás számlálója fut, akkor megszakítja ezt a visszaszámlálást

        if (new Date() > JelentkKezdeteDate)
        {
            document.getElementById('visszaszamlalodiv').style.display = 'none';
            document.getElementById('jelentkezgombdiv').style.display = 'block';
        }
        else
        {
            document.getElementById('visszaszamlalodiv_text').innerHTML = GetTimeDifForModalVisszaszamlalo(JelentkKezdeteDate, new Date());
            setTimeout(function ()
            {
                JelntKezdeteVisszaszamolo(JelentkKezdeteDate, id);
            }, 999);
        }
    }

    function ShowModal(id, kiirta, musznev, idokezd, idoveg, letszam, pont, mospont, kornev, megj, jelaktiv, jelentk_kezdete, jelentk_visszaszaml_indok, felvetel)
    {
        document.getElementById('modalheadertext').innerHTML = musznev + ' Jelentkezés';
        document.getElementById('modalheaderlink').href = '/muszedit/?muszid=' + id;
        document.getElementById('modalkiirta').innerHTML = 'Kiírta: ' + kiirta;
        document.getElementById('modalidokezd').innerHTML = 'Kezdet: ' + idokezd;
        document.getElementById('modalidovegERTEK').innerHTML = idoveg;
        document.getElementById('modalletszam').innerHTML = 'Maximális létszám: ' + letszam;
        document.getElementById('modalpont').innerHTML = 'Közösségi pont: ' + pont;
        document.getElementById('modalmospont').innerHTML = 'Pont mosogatásért: ' + mospont;
        document.getElementById('modalertekelokornev').innerHTML = 'Értékelő kör: ' + kornev;
        document.getElementById('modalmegj').innerHTML = 'Wukker intelmei: ' + megj;
        document.getElementById('modalmuszakid').value = id;


        document.getElementById('visszaszamlalodiv').style.display = 'none';

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
            var iPhoneSafeDateArr = jelentk_kezdete.split(/[- :]/);
            var JelentkKezdeteDate = new Date(iPhoneSafeDateArr[0], iPhoneSafeDateArr[1] - 1, iPhoneSafeDateArr[2], iPhoneSafeDateArr[3], iPhoneSafeDateArr[4], iPhoneSafeDateArr[5]);
            if (felvetel == 0 || new Date() > JelentkKezdeteDate)
            {
                document.getElementById('jelentkezgombdiv').style.display = 'block';
            }
            else
            {
                if (jelentk_visszaszaml_indok == 'pontszam')
                {
                    document.getElementById('visszaszamlalo_indok_header').innerHTML = "A pontszámodból adódóan ennyi idő múlva jelentkezhetsz:";
                }
                else
                {
                    document.getElementById('visszaszamlalo_indok_header').innerHTML = "Jelenleg van más aktív jelentkezésed, így ide csak <?php echo (\Eszkozok\GlobalSettings::GetSetting('mas_muszakra_ennyivel_elotte_jelentkezhet') / (60*60)); ?> órával a műszak előtt jelentkezhetsz.";

                }

                document.getElementById('jelentkezgombdiv').style.display = 'none';
                document.getElementById('visszaszamlalodiv').style.display = 'block';
                RunningVisszaszamlaloID = id;
                JelntKezdeteVisszaszamolo(JelentkKezdeteDate, id);
            }
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
