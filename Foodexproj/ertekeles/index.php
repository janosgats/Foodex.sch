<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';
require_once __DIR__ . '/../Eszkozok/PicturesHelper.php';

\Eszkozok\LoginValidator::Ertekelo_DiesToErrorrPage();

$OsszesEddigiErtekelesMegjelenit = false;
if (isset($_REQUEST['osszert']) && $_REQUEST['osszert'] == 1)
    $OsszesEddigiErtekelesMegjelenit = true;

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
    <title>Fx - Értékelés</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">


    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel="stylesheet" href="../css/modalimage.css">


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
</head>

<body style="background: #de520d">
<div class="container">

    <?php
    NavBar::echonavbar('ertekeles');
    ?>

    <div class="panel panel-default">


        <div class="panel-heading" style="width:100%;justify-content: space-between;display: flex;flex-flow: row;align-items: center;">
            <a href="?osszert=0" style="text-decoration: none">
                <button type="button" class="btn btn-<?= (!$OsszesEddigiErtekelesMegjelenit) ? 'default' : 'success '; ?>" <?= (!$OsszesEddigiErtekelesMegjelenit) ? ' disabled="disabled" ' : ''; ?>
                        style="<?= (!$OsszesEddigiErtekelesMegjelenit) ? ' border-color:black; ' : ''; ?>margin: 5px;white-space: normal; min-height: 70px;">
                    Általad jelenleg értékelhető műszakok
                </button>
            </a>
            <a href="?osszert=1" style="text-decoration: none">
                <button type="button" class="btn btn-<?= ($OsszesEddigiErtekelesMegjelenit) ? 'default' : 'success '; ?>" <?= ($OsszesEddigiErtekelesMegjelenit) ? ' disabled="disabled" ' : ''; ?>
                        style="<?= ($OsszesEddigiErtekelesMegjelenit) ? ' border-color:black; ' : ''; ?>margin: 5px;white-space: normal; min-height: 70px;">Az
                    általad eddig írt összes értékelés
                </button>
            </a>

            <!--            <div style="display: inline-block; min-width: 200px; text-align: center">-->
            <!--            <span style="padding: 10px; margin: 10px; text-align: center">-->
            <!--            <b style="align-self: center">Általad jelentleg értékelhető műszakok Foodexesei</b>-->
            <!--                </span>-->
            <!--            </div>-->
            <!---->
            <!--            <div style="display: inline-block;min-width: 200px; text-align: center">-->
            <!--            <span style="padding: 10px; margin: 10px; text-align: center">-->
            <!--            <b>Az általad eddig írt összes értékelés</b>-->
            <!--                </span>-->
            <!--            </div>-->
        </div>
        <div class="panel-body">
            <table class="table table-hover">
                <?php
                try
                {
                $conn = \Eszkozok\Eszk::initMySqliObject();

                if (!$OsszesEddigiErtekelesMegjelenit)
                {

                    $ErtekelhetoKorIDk = \Eszkozok\LoginValidator::GetErtekeloKorokIdk();

                    $KikVittekAMuszakokat = [];
                    $stmt = $conn->prepare("SELECT fxaccok.nev, fxaccok.internal_id, fxjelentk.muszid, ertekelesek.id AS ert_id
                                            FROM   fxjelentk INNER JOIN
                                            (
                                            SELECT fxmuszakok.korid, muszid, idoveg, letszam, GROUP_CONCAT(jelentkezo ORDER BY jelido ASC) AS grouped_jelentkezo
                                            FROM     fxjelentk
                                            JOIN fxmuszakok ON fxjelentk.muszid = fxmuszakok.ID
                                            WHERE fxjelentk.status = 1
                                            GROUP BY muszid
                                            ) AS group_max
                                            ON fxjelentk.muszid = group_max.muszid AND FIND_IN_SET(jelentkezo, grouped_jelentkezo) <= group_max.letszam
                                            JOIN fxaccok ON fxjelentk.jelentkezo = fxaccok.internal_id
                                            LEFT JOIN ertekelesek ON ertekelesek.ertekelt = fxjelentk.jelentkezo AND ertekelesek.muszid = group_max.muszid AND ertekelesek.ertekelo = '" . $conn->escape_string($_SESSION['profilint_id']) . "'
                                            WHERE STATUS = 1
                                            AND group_max.idoveg < NOW()
                                            AND group_max.korid IN (" . implode(',', $ErtekelhetoKorIDk) . ")
                                            ORDER BY fxjelentk.muszid, fxjelentk.jelido ASC;");


                    if (!$stmt->execute())
                        throw new \Exception('$stmt->execute() 1 is false!');
                    $res = $stmt->get_result();

                    while ($row = $res->fetch_assoc())
                    {
                        $KikVittekAMuszakokat[] = $row;
                    }

                    $Count_KikVittekAMuszakokat = count($KikVittekAMuszakokat);

                    $stmt = $conn->prepare("SELECT * FROM fxmuszakok WHERE korid IN (" . implode(',', $ErtekelhetoKorIDk) . ") AND idoveg < NOW() ORDER BY fxmuszakok.idokezd DESC;");

                    if (!$stmt->execute())
                        throw new \Exception('$stmt->execute() 2 is false!');

                    $res = $stmt->get_result();

                    while ($row = $res->fetch_assoc())
                    {
                        ?>
                        <tr>
                            <td>
                                <div style="width: 100%">
                                    <div style="width: 100%; text-align: center">
                                        <h3 style="margin: 0"><?= $row['musznev']; ?></h3>

                                        <p><?= $row['idokezd']; ?></p>
                                    </div>
                                    <div style="width: 100%; text-align: center;">

                                        <?php
                                        for ($i = 0; $i < $Count_KikVittekAMuszakokat; ++$i)
                                        {
                                            if ($KikVittekAMuszakokat[$i]['muszid'] == $row['ID'])
                                            {
                                                ?>
                                                <div style="display: inline-block; padding: 20px; vertical-align:top;">
                                                    <div style="float: top; margin-top: 0; margin-bottom: auto">
                                                        <img class="imageForModal" onclick="ImageOnClickShowModal(this);" alt="<?= htmlentities($KikVittekAMuszakokat[$i]['nev']); ?>"
                                                             src="<?= htmlentities(\Eszkozok\PicturesHelper::getProfilePicURLForInternalID($KikVittekAMuszakokat[$i]['internal_id'])); ?>" width="160px"/>

                                                        <a style="cursor: pointer;" href="../profil/?mprof=<?= htmlentities($KikVittekAMuszakokat[$i]['internal_id']); ?>"><p
                                                                style="max-width: 220px;"><?= $KikVittekAMuszakokat[$i]['nev']; ?></p>
                                                        </a>
                                                    </div>

                                                    <a href="editert/?vissza_param_osszert=0&muszid=<?= urlencode($row['ID']); ?>&ertekelt_int_id=<?= urlencode($KikVittekAMuszakokat[$i]['internal_id']); ?>">
                                                        <?php

                                                        if ($KikVittekAMuszakokat[$i]['ert_id'] == null)
                                                        {
                                                            ?>
                                                            <button type="button" class="btn btn-success">Értékelem</button>
                                                            <?php
                                                        }
                                                        else
                                                        {
                                                            ?>
                                                            <button type="button" class="btn btn-info">Módosítom</button>
                                                            <?php
                                                        }
                                                        ?>
                                                    </a>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                }
                else if ($OsszesEddigiErtekelesMegjelenit)
                {
                $stmt = $conn->prepare("SELECT fxmuszakok.ID AS muszid, musznev, idokezd, internal_id, fxaccok.nev AS acc_nev
                                                                    FROM ertekelesek
                                                                    JOIN fxaccok ON fxaccok.internal_id = ertekelesek.ertekelt
                                                                    JOIN fxmuszakok ON fxmuszakok.ID = ertekelesek.muszid
                                                                    WHERE ertekelo = ?
                                                                    ORDER BY fxmuszakok.idokezd DESC, fxmuszakok.ID DESC;");
                $stmt->bind_param('s', $_SESSION['profilint_id']);


                if (!$stmt->execute())
                    throw new \Exception('$stmt->execute() 2 is false!');

                $res = $stmt->get_result();

                $ElozoMuszid = null;
                while ($row = $res->fetch_assoc())
                {
                if ($ElozoMuszid != $row['muszid'])
                {
                if ($ElozoMuszid != null)
                {
                ?>
        </div>
    </div>
    </td>
    </tr>
    <?php
    }
    ?>

    <tr>
        <td>
            <div style="width: 100%">
                <div style="width: 100%; text-align: center">
                    <h3 style="margin: 0"><?= $row['musznev']; ?></h3>

                    <p><?= $row['idokezd']; ?></p>
                </div>
                <div style="width: 100%; text-align: center;">
                    <?php
                    $ElozoMuszid = $row['muszid'];
                    }
                    ?>
                    <div style="display: inline-block; padding: 20px; vertical-align:top;">
                        <div style="float: top; margin-top: 0; margin-bottom: auto">
                            <img class="imageForModal" onclick="ImageOnClickShowModal(this);" alt="<?= htmlentities($row['acc_nev']); ?>"
                                 src="<?= htmlentities(\Eszkozok\PicturesHelper::getProfilePicURLForInternalID($row['internal_id'])); ?>" width="160px"/>

                            <a style="cursor: pointer;" href="../profil/?mprof=<?php echo $row['internal_id']; ?>"><p
                                    style="max-width: 220px;"><?= htmlentities($row['acc_nev']); ?></p>
                            </a>
                        </div>

                        <a href="editert/?vissza_param_osszert=1&muszid=<?= urlencode($row['muszid']); ?>&ertekelt_int_id=<?= urlencode($row['internal_id']); ?>">
                            <button type="button" class="btn btn-info">Módosítom</button>
                        </a>
                    </div>
                    <?php
                    }

                    }

                    }
                    catch
                    (\Exception $e)
                    {
                        Eszkozok\Eszk::dieToErrorPage('34318: ' . $e->getMessage());
                    }
                    ?>
                    </table>
                </div>
            </div>


</div>

<!-- The Modal -->
<div id="myModal" class="modal" onclick="this.style.display = 'none';">
    <span class="close">&times;</span>
    <img class="modal-content" id="img01" style="height: 80%;width: auto">

    <div id="caption"></div>
</div>

<script>
    // Get the modal
    var modal = document.getElementById("myModal");

    // Get the image and insert it inside the modal - use its "alt" text as a caption

    var modalImg = document.getElementById("img01");
    var captionText = document.getElementById("caption");
    function ImageOnClickShowModal(imgelement)
    {
        modal.style.display = "block";
        modalImg.src = imgelement.src;
        captionText.innerHTML = imgelement.alt;
    }

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on <span> (x), close the modal
    span.onclick = function ()
    {
        modal.style.display = "none";
    }
</script>

</body>
</html>