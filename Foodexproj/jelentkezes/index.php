<?php
session_start();

require_once '../Eszkozok/Eszk.php';
require_once '../Eszkozok/param.php';
include_once '../profil/Profil.php';
include_once 'jelentkez.php';

\Eszkozok\Eszk::ValidateLogin();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

doJelentkezes();
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Fx - Jelentkezés Műszakra</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="main.css">

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
        </colgroup>

        <?php
        try {
            $conn = Eszkozok\Eszk::initMySqliObject();


            if (!$conn)
                throw new \Exception('SQL hiba: $conn is \'false\'');

            ///`fxmuszakok` (`kiirta`, `musznev`, `idokezd`, `idoveg`, `letszam`, `pont`)
            $stmt = $conn->prepare("SELECT * FROM `fxmuszakok` WHERE `idokezd` >= CURDATE() ORDER BY `idokezd` DESC;");
            if (!$stmt)
                throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);


            if ($stmt->execute()) {
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
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

                        for ($i = 0; $i < count($jelnevtomb);) {
                            if ($i < $row['letszam'])
                                $jelnevstring .= '<p class="varolistaElso">';
                            else
                                $jelnevstring .= '<p>';


                            $jelnevstring .= htmlspecialchars($jelnevtomb[$i]);

                            $jelnevstring .= '</p>';

                            ++$i;

                            if ($i < count($jelnevtomb))
                                $jelnevstring .= ', ';
                        }
                        ?>

                        <tr class="tablaSor">
                            <td class="tablaCella oszlopNev">
                                <p><?php echo htmlspecialchars($row['musznev']); ?></p>
                            </td>
                            <td class="tablaCella oszlopReszletek">
                                <p onclick="ShowModal('<?php echo $row['ID']; ?>','<?php echo htmlspecialchars($kiiroProfil->getNev()); ?>', '<?php echo $row['musznev']; ?>', '<?php echo $idokezd->format('Y-m-d     H:i'); ?>', '<?php echo $idoveg->format('Y-m-d     H:i'); ?>', '<?php echo htmlspecialchars($row['letszam']); ?>', '<?php echo htmlspecialchars($row['pont']); ?>','<?php echo htmlspecialchars($row['mospont']); ?>', '<?php echo $jelentkIdoszakVan; ?>', '<?php echo $felvetel; ?>');">
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
                        </tr>
                        <?php
                    }
                }
            } else {
                throw new \Exception('Az SQL parancs végrehajtása nem sikerült.' . ' :' . $conn->error);
            }
        } catch (\Exception $e) {
            ob_clean();
            Eszkozok\Eszk::dieToErrorPage('3014: ' . $e->getMessage());
        }
        ?>

    </table>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalheadertext">Jelentkezés</h4>
            </div>
            <div class="modal-body">
                <p id="modalkiirta">Kiírta: </p>
                <p id="modalidokezd">Kezdet: </p>
                <p id="modalidovegSZOVEG">Vég: </p>
                <p id="modalidovegERTEK"></p>
                <p id="modalletszam">Maximális létszám: </p>
                <p id="modalpont">Közösségi pont: </p>
            </div>
            <div class="modal-footer">
                <form method="post" id="jelentkezgombdiv">
                    <div class="g-recaptcha" style=" margin: 0 auto;display: inline-block;" data-callback="greDataCallback"
                         data-sitekey="6LfTxl8UAAAAAO05DCRMYxdnDnRHd5E-uzN-J8fs"></div>
                    <input name="muszid" id="modalmuszakid" style="display: none">
                    <input name="muszmuv" value="felvetel" id="modalmuvelet" style="display: none">

                    <div class="tooltip" id="modalbtntooltip">
                        <span class="tooltiptext" id="modalbtntooltiptext">Oldd meg a reCaptcha-t!</span>
                        <button type="submit" class="btn btn-primary popupbutton" id="modalsubmitbtn" disabled>Viszem!</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script>
    function greDataCallback() {
        document.getElementById('modalsubmitbtn').removeAttribute('disabled');
        document.getElementById('modalbtntooltip').classList.remove('tooltip');
        document.getElementById('modalbtntooltiptext').style.display = 'none';
    }

    function ShowModal(id, kiirta, musznev, idokezd, idoveg, letszam, pont, mospont, jelaktiv, felvetel) {

        document.getElementById('modalheadertext').innerHTML = musznev + ' Jelentkezés';
        document.getElementById('modalkiirta').innerHTML = 'Kiírta: ' + kiirta;
        document.getElementById('modalidokezd').innerHTML = 'Kezdet: ' + idokezd;
        document.getElementById('modalidovegERTEK').innerHTML = idoveg;
        document.getElementById('modalletszam').innerHTML = 'Maximális létszám: ' + letszam;
        document.getElementById('modalpont').innerHTML = 'Közösségi pont: ' + pont;
        document.getElementById('modalmuszakid').value = id;

        if (felvetel == 0) {
            document.getElementById('modalmuvelet').value = 'lead';
            document.getElementById('modalsubmitbtn').innerHTML = 'Leadom!';
        }
        else {
            document.getElementById('modalmuvelet').value = 'felvesz';
            document.getElementById('modalsubmitbtn').innerHTML = 'Viszem!';
        }

        if (jelaktiv == 0) {
            document.getElementById('jelentkezgombdiv').style.display = 'none';
        }
        else {
            document.getElementById('jelentkezgombdiv').style.display = 'block';
        }
    }
</script>

</body>
</html>
