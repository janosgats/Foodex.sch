<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';

\Eszkozok\LoginValidator::Ertekelo_DiesToErrorrPage();

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
    NavBar::echonavbar('ertekeles');
    ?>

    <div class="panel panel-default">
        <div class="panel-heading" style="text-align: center"><b>Általad értékelhető műszakok Foodexesei</b></div>
        <div class="panel-body">
            <table class="table table-hover">
                <?php
                try
                {
                    $conn = \Eszkozok\Eszk::initMySqliObject();

                    $ErtekelhetoKorIDk = \Eszkozok\LoginValidator::GetErtekeloKorokIdk();


                    $stmt = $conn->prepare("SELECT * FROM fxmuszakok WHERE korid IN (" . implode(',', $ErtekelhetoKorIDk) . ") ORDER BY fxmuszakok.idokezd DESC;");

                    if (!$stmt->execute())
                        throw new \Exception('$stmt->execute() 1 is false!');

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
                                        <div style="display: inline-block; padding: 20px; vertical-align:top;">
                                            <div style="float: top; margin-top: 0; margin-bottom: auto">
                                            <img src="../res/kepek/default_profile_picture.jpg" width="160px"/>
                                            <p style="max-width: 220px;">Példa Béla Gyurika dg sdfg sdg sfdg dfsgfsdg</p>
                                            </div>
                                            <button type="button" class="btn btn-success">Értékelem</button>
                                        </div>
                                        <div style="display: inline-block; padding: 20px; vertical-align:top; ">
                                            <img src="../res/kepek/default_profile_picture.jpg" width="160px"/>
                                            <p style="max-width: 220px;">Példa Béla Gyurika Gecihosszúnév Méghosszabb</p>
                                            <button type="button" class="btn btn-success">Értékelem</button>
                                        </div>
                                        <div style="display: inline-block; padding: 20px;  vertical-align:top;">
                                            <img src="../res/kepek/default_profile_picture.jpg" width="160px"/>
                                            <p style="max-width: 220px;">Példa Béla Gyurika fdg sfdg fd fsdg fs sdgfgdgads</p>
                                            <button type="button" class="btn btn-success">Értékelem</button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }

                }
                catch (\Exception $e)
                {
                    Eszkozok\Eszk::dieToErrorPage('34018: ' . $e->getMessage());
                }
                ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>