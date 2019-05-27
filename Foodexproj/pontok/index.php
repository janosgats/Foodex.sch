<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';

\Eszkozok\LoginValidator::AccountSignedIn();

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
    NavBar::echonavbar('pontok');
    ?>

    <div class="panel panel-default">
        <div class="panel-heading">Ponttáblázat</div>
        <div class="panel-body">
            <table class="table table-hover">
                <?php
                try
                {
                    $conn = \Eszkozok\Eszk::initMySqliObject();


                    $stmtKomp = $conn->prepare("SELECT internal_id, SUM(pont) AS SumPont FROM kompenz WHERE ( `ido` BETWEEN '" . \Eszkozok\GlobalSettings::GetSetting('pontozasi_idoszak_kezdete') . "' AND '" . \Eszkozok\GlobalSettings::GetSetting('pontozasi_idoszak_vege') . "' ) GROUP BY internal_id;");

                    if (!$stmtKomp->execute())
                        throw new \Exception('$stmt->execute() 1 is false!');

                    $resKomp = $stmtKomp->get_result();
                    $Kompenzalasok = [];
                    while ($row = $resKomp->fetch_assoc())
                    {
                        $Kompenzalasok[$row['internal_id']] = $row['SumPont'];
                    }


                    $stmtMuszak = $conn->prepare("SELECT fxaccok.nev, fxaccok.internal_id
                                                ,
                                                SUM(fxmuszakok.pont) AS MuszakPont,
                                                SUM(CASE     WHEN ErvenyesJelentkezesek.mosogat = 1     THEN fxmuszakok.mospont    ELSE 0 END) AS MosogatasPont
                                                FROM fxaccok
                                                LEFT JOIN
                                                (
                                                  SELECT fxjelentk.*
                                                  FROM   fxjelentk INNER JOIN
                                                  (
                                                    SELECT   muszid, letszam, GROUP_CONCAT(jelentkezo ORDER BY jelido ASC) AS grouped_jelentkezo
                                                    FROM     fxjelentk
                                                    JOIN fxmuszakok ON fxjelentk.muszid = fxmuszakok.ID
                                                    WHERE fxjelentk.status = 1
                                                    GROUP BY muszid
                                                  ) AS group_max
                                                  ON fxjelentk.muszid = group_max.muszid AND FIND_IN_SET(jelentkezo, grouped_jelentkezo) <= group_max.letszam
                                                  WHERE status = 1
                                                  ORDER BY fxjelentk.muszid, fxjelentk.jelido ASC
                                                ) AS ErvenyesJelentkezesek
                                                ON fxaccok.internal_id = ErvenyesJelentkezesek.jelentkezo
                                                LEFT JOIN fxmuszakok ON ErvenyesJelentkezesek.muszid = fxmuszakok.ID
                                                AND (fxmuszakok.`idoveg` < NOW()
                                                AND ( fxmuszakok.`idokezd` BETWEEN '" . \Eszkozok\GlobalSettings::GetSetting('pontozasi_idoszak_kezdete') . "' AND '" . \Eszkozok\GlobalSettings::GetSetting('pontozasi_idoszak_vege') . "' ))
                                                GROUP BY fxaccok.internal_id
                                                ORDER BY fxaccok.nev ASC;");

                    if (!$stmtMuszak->execute())
                        throw new \Exception('$stmt->execute() 1 is false!');

                    $resMuszak = $stmtMuszak->get_result();

                    while ($rowMuszak = $resMuszak->fetch_assoc())
                    {
                        $muszpont =  round($rowMuszak['MuszakPont'] ?: 0, 1);
                        $mospont = round($rowMuszak['MosogatasPont']?: 0, 1);
                        $komppont = round(isset($Kompenzalasok[$rowMuszak['internal_id']]) ? $Kompenzalasok[$rowMuszak['internal_id']] : 0, 1);
                        $sumpont = round($muszpont + $mospont + $komppont, 1);
                        ?>
                        <tr>
                            <td>

                                <a style="cursor: pointer" href="<?php echo '../profil/?mprof=' . $rowMuszak['internal_id']; ?>"><p><?php echo htmlentities($rowMuszak['nev']); ?></p></a>
                            </td>
                            <td>
                                <a class="badge" href="userpont/?int_id=<?php echo $rowMuszak['internal_id']; ?>"><?php echo htmlentities($sumpont . ' pont = ' . $muszpont . (($mospont >= 0)?' + ':' - ') . abs($mospont) . (($komppont >= 0)?' + ':' - ') . abs($komppont)); ?></a>
                            </td>
                        </tr>
                        <?php
                    }

                }
                catch (\Exception $e)
                {
                    Eszkozok\Eszk::dieToErrorPage('3018: ' . $e->getMessage());
                }
                ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>