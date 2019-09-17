<?php
require_once __DIR__ . '/Eszkozok/Eszk.php';
require_once __DIR__ . '/Eszkozok/SMTPSender.php';

$reason = null;

if (isset($_REQUEST['reason']) && $_REQUEST['reason'] != '')
    $reason = $_REQUEST['reason'];

$JelentkezesEredmeny = 'hiba';
if ($reason == 'ertekelojelentkezes') {
    $conn = new \mysqli();
    try {
        session_start();

        if (!isset($_SESSION['BelepesjogKero-internal_id']) || !isset($_SESSION['BelepesjogKero-nev']) || !isset($_SESSION['BelepesjogKero-email']))
            throw new Exception();

        if ($_SESSION['BelepesjogKero-internal_id'] == '' || $_SESSION['BelepesjogKero-nev'] == '' || $_SESSION['BelepesjogKero-email'] == '')
            throw new Exception();


        $conn = \Eszkozok\Eszk::initMySqliObject();


        $stmt = $conn->prepare("SELECT * FROM fxaccok WHERE `internal_id` = ?;");
        $stmt->bind_param('s', $_SESSION['BelepesjogKero-internal_id']);

        if (!$stmt->execute())
            throw new \Exception();

        $result = $stmt->get_result();
        if ($result->num_rows != 0) {
            $JelentkezesEredmeny = 'marjelentkezett';
            throw new Exception();
        }

        $stmt = $conn->prepare("INSERT INTO fxaccok (internal_id, nev, email) VALUES (?,?,?);");
        $stmt->bind_param('sss', $_SESSION['BelepesjogKero-internal_id'], $_SESSION['BelepesjogKero-nev'], $_SESSION['BelepesjogKero-email']);

        if (!$stmt->execute())
            throw new \Exception();

        if ($stmt->affected_rows != 1)
            throw new Exception();

        SMTPSender::sendNewErtekeloJelentkezesMailToAdmins($_SESSION['BelepesjogKero-nev']);

        $JelentkezesEredmeny = 'siker';
    }
    catch (\Exception $e) {

    }
    finally {
        try {
            $conn->close();
        }
        catch (\Exception $ex) {

        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Nézelődnél?</title>

    <link rel="stylesheet" href="nemkortag.css">

    <link rel="icon" href="res/kepek/favicon1_64p.png">
</head>

<body style="background-size: cover;" background="res/kepek/sad_broken_egg.svg">

<div>
    <div style="text-align: center; left: 50%; position: absolute;top: 50%;-ms-transform: translate(-50%, -50%);transform: translate(-50%, -50%);">
        <div style="background-color: #FFFFFFDD; padding: 50px; display: inline-block">
            <?php
            switch ($reason) {
                case 'ertekelojelentkezes': {
                    if ($JelentkezesEredmeny == 'siker') {
                        ?>

                        <h1 style="text-align: center;color: #999999;">Sikeresen jelentkeztél!</h1>
                        <h2 style="text-align: center;color: #999999;">Hamarosan elbírálunk.</h2>

                        <a href="<?= \Eszkozok\Eszk::GetRootURL(); ?>" style="color: white;text-decoration:none;">
                            <div class="cube flip-to-bottom">
                                <div class="default-state" style="background: #f46000">
                                    <span>Vissza!</span>
                                </div>
                                <div class="active-state" style="background: #f46000"><span>A főoldalra...</span></div>
                            </div>
                        </a>
                        <?php
                    } else if ($JelentkezesEredmeny == 'marjelentkezett') {
                        ?>

                        <h1 style="text-align: center;color: #999999;">Korábban már jelentkeztél.</h1>
                        <h2 style="text-align: center;color: #999999;">Ha sürgős, baszogasd a körvezt!</h2>

                        <a href="<?= \Eszkozok\Eszk::GetRootURL(); ?>" style="color: white;text-decoration:none;">
                            <div class="cube flip-to-bottom">
                                <div class="default-state" style="background: #f46000">
                                    <span>Vissza!</span>
                                </div>
                                <div class="active-state" style="background: #f46000"><span>A főoldalra...</span></div>
                            </div>
                        </a>
                        <?php
                    } else {

                        ?>

                        <h1 style="text-align: center;color: #999999;">A jelentkezés sikertelen.<br>Térj vissza később!</h1>

                        <a href="<?= \Eszkozok\Eszk::GetRootURL(); ?>" style="color: white;text-decoration:none;">
                            <div class="cube flip-to-bottom">
                                <div class="default-state" style="background: #f46000">
                                    <span>Vissza!</span>
                                </div>
                                <div class="active-state" style="background: #f46000"><span>A főoldalra...</span></div>
                            </div>
                        </a>
                        <?php

                    }
                }
                    break;
                case 'nembelephet': {
                    ?>
                    <h1 style="text-align: center;color: #999999;">Jelenleg nincs jogod a belépésre!</h1>

                    <a href="<?= \Eszkozok\Eszk::GetRootURL(); ?>" style="color: white;text-decoration:none;">
                        <div class="cube flip-to-bottom">
                            <div class="default-state" style="background: #f46000">
                                <span>Vissza!</span>
                            </div>
                            <div class="active-state" style="background: #f46000"><span>A főoldalra...</span></div>
                        </div>
                    </a>
                    <?php
                }
                    break;
                default: {
                    ?>

                    <h1 style="text-align: center;color: #999999;padding-top:0">A PéK szerint nem vagy
                        <span style="display: inline;letter-spacing: -3px">Food<p style="display: inline;color: #f4511e; margin-left: -2px">Ex</p> </span>
                        tag.
                    </h1>
                    <br><br>

                    <a href="mailto:foodex@sch.bme.hu" style="color: white;text-decoration:none;">
                        <div class="cube flip-to-bottom">
                            <div class="default-state">
                                <span>Csatlakozz!</span>
                            </div>
                            <div class="active-state"><span>Érdemes...</span></div>
                        </div>
                    </a>
                    <br><br>
                    <a href="?reason=ertekelojelentkezes" style="color: white;text-decoration:none;">
                        <div class="cube flip-to-bottom">
                            <div class="default-state" style="background: #FFA500">
                                <span>Értékelj!</span>
                            </div>
                            <div class="active-state" style="background: #FF9500"><span>Hasznos...</span></div>
                        </div>
                    </a>
                    <?php
                }
                    break;
            }
            ?>
        </div>
    </div>
</div>

</body>

</html>