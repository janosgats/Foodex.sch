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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>

<body style="background: #de520d">
<div class="container">
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"
                        aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="../profil"><img alt="Brand" src="../res/kepek/FoodEx_logo.png" style="height: 30px"></a>
            </div>

            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li class="active"><a href="../jelentkezes">Jelentkezés műszakra <span class="sr-only">(current)</span></a></li>
                    <li><a href="../pontok/userpont/?mosjelentk=1">Mosogattam!</a></li>
                    <li><a href="../pontok">Pontozás</a></li>
                    <?php
                    if ($AktProfil->getUjMuszakJog() == 1) {
                        ?>
                        <li><a href="../ujmuszak">Új műszak kiírása</a></li>
                        <?php
                    }
                    ?>
                </ul>
                <ul class="nav navbar-nav navbar-right p-t" style="margin-top: 8px">
                    <li>
                        <form action="logout.php">
                            <button type="submit" class="btn btn-danger">Kijelentkezés</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="panel panel-default">
        <div class="panel-heading">Műszakok</div>
        <div class="panel-body">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Műszak</th>
                    <th>Időpont</th>
                    <th>Létszám</th>
                    <th>Akik jelentkeztek</th>
                    <th></th>
                </tr>
                </thead>
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

                                <tr>
                                    <td><?php echo htmlspecialchars($row['musznev']); ?></td>
                                    <td><?php echo $idostringbuff; ?></td>
                                    <td><?php echo htmlspecialchars($row['letszam']); ?> fő</td>
                                    <td><?php echo $jelnevstring; ?></td>
                                    <td>
                                        <a href="#"><i onclick="ShowModal('<?php echo $row['ID']; ?>','<?php echo htmlspecialchars($kiiroProfil->getNev()); ?>', '<?php echo $row['musznev']; ?>', '<?php echo $idostringbuff; ?>', '<?php echo $idoveg->format('Y-m-d     H:i'); ?>', '<?php echo htmlspecialchars($row['letszam']); ?>', '<?php echo htmlspecialchars($row['pont']); ?>','<?php echo htmlspecialchars($row['mospont']); ?>', '<?php echo $jelentkIdoszakVan; ?>', '<?php echo $felvetel; ?>');"
                                                       class="fa fa-plus-square-o fa-2x"></i></a>
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
    </div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalheadertext"></h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Időpont</label>
                        <div class="col-sm-8">
                            <p class="form-control-static" id="modalidokezd"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Maximális létszám</label>
                        <div class="col-sm-8">
                            <p class="form-control-static" id="modalletszam"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <form method="post" id="jelentkezgombdiv">
                    <div class="g-recaptcha" style=" margin: 0 auto;display: inline-block;" data-callback="greDataCallback"
                         data-sitekey="6LfTxl8UAAAAAO05DCRMYxdnDnRHd5E-uzN-J8fs"></div>
                    <input type="hidden" name="muszid" id="modalmuszakid">
                    <input type="hidden" name="muszmuv" value="felvetel" id="modalmuvelet">

                    <div class="tooltip" id="modalbtntooltip">
                        <span class="tooltiptext" id="modalbtntooltiptext">Oldd meg a reCaptcha-t!</span>
                        <button type="submit" class="btn btn-primary popupbutton" id="modalsubmitbtn" disabled>Viszem!</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src='https://www.google.com/recaptcha/api.js'></script>
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
        jQuery('#myModal').modal();
        document.getElementById('modalheadertext').innerHTML = 'Jelentkezés ' + musznev + ' műszakra';
        document.getElementById('modalidokezd').innerHTML = idokezd;
        // document.getElementById('modalidovegERTEK').innerHTML = idoveg;
        document.getElementById('modalletszam').innerHTML = letszam + ' fő';
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
