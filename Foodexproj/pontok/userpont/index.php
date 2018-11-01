<?php
session_start();

require_once __DIR__ . '/../../Eszkozok/Eszk.php';
require_once __DIR__ . '/../../Eszkozok/param.php';
require_once __DIR__ . '/../../profil/Profil.php';

\Eszkozok\Eszk::ValidateLogin();
$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

$MosogatasJelentkezes = 0;//1: Ha az aktuális profil akar műszak után mosogatásra jelentkezni

if (IsURLParamSet('mosjelentk') && GetURLParam('mosjelentk') == 1) {
    SetURLParam('int_id', $_SESSION['profilint_id']);

    $MosogatasJelentkezes = 1;
} elseif (!IsURLParamSet('int_id')) {
    Eszkozok\Eszk::RedirectUnderRoot('pontok');
}


$MegjelenitettProfil = \Eszkozok\Eszk::GetTaroltProfilAdat(GetURLParam('int_id'));

$mosfoglalt = false;

if ($MosogatasJelentkezes) {
    if (IsURLParamSet('muv') && (GetURLParam('muv') == 'ujmosjel' || GetURLParam('muv') == 'ujmoslead')) {
        if (IsURLParamSet('mosmuszid')) {
            try {
                $mosmuszid = GetURLParam('mosmuszid');

                $conn = \Eszkozok\Eszk::initMySqliObject();
                if (!$conn)
                    throw new \Exception('SQL hiba: $conn is \'false\'');


                if (\Eszkozok\Eszk::BenneVanEAKeretbenWithConn($mosmuszid, $_SESSION['profilint_id'], $conn)) {
                    $stmt = $conn->prepare("SELECT COUNT(1) FROM `fxmuszakok` WHERE  `idoveg` < NOW() AND `ID` = ?;");
                    if (!$stmt)
                        throw new \Exception('SQL hiba: $stmt 1 is \'false\'' . ' :' . $conn->error);

                    $stmt->bind_param('i', $mosmuszid);

                    if ($stmt->execute()) {
                        $resultMuszak = $stmt->get_result();
                        if ($resultMuszak->num_rows == 1) {//Az acc a műszakkeretnek tagja, és már a műszak végideje is elmúlt



                            if (GetURLParam('muv') == 'ujmosjel')
                            {
                                $stmt = $conn->prepare("SELECT `ID` FROM `fxjelentk` WHERE muszid = ? AND `status` = 1 AND `mosogat` = 1;");

                                $stmt->bind_param('i', $mosmuszid);

                                if ($stmt->execute())
                                {
                                    $resultMuszak = $stmt->get_result();
                                    if ($resultMuszak->num_rows != 0)
                                        $mosfoglalt = true;


                                }
                                else
                                    throw new \Exception('$stmt->execute() 2 nem sikerült');


                                if(!$mosfoglalt)
                                {
                                    $stmt = $conn->prepare("UPDATE `fxjelentk` SET  `mosogat` = '1' WHERE `jelentkezo` = ? AND `muszid` = ? AND `status` = 1;");
                                }
                            }
                            elseif (GetURLParam('muv') == 'ujmoslead')
                                $stmt = $conn->prepare("UPDATE `fxjelentk` SET  `mosogat` = '0' WHERE `jelentkezo` = ? AND `muszid` = ? AND `status` = 1;");

                            if(!$mosfoglalt)
                            {
                                if (!$stmt)
                                    throw new \Exception('SQL hiba: $stmt 2 is \'false\'');

                                $intid = $_SESSION['profilint_id'];
                                $stmt->bind_param('si', $intid, $mosmuszid);

                                if ($stmt->execute())
                                {

                                }
                                else
                                    throw new \Exception('$stmt->execute() 2 nem sikerült');
                            }
                        }
                    } else
                        throw new \Exception('$stmt->execute() 1 nem sikerült' . ' :' . $conn->error);

                }
            } catch (\Exception $e) {
                self::dieToErrorPage('3003: ' . $e->getMessage());
            } finally {
                try {
                    $conn->close();
                } catch (\Exception $e) {
                }
            }
        }
    }

}

//var_dump($MegjelenitettProfil);

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Fx - <?php echo ($MosogatasJelentkezes) ? 'Mosogatás' : 'Pontok'; ?></title>

    <link rel="icon" href="../../res/kepek/favicon1_64p.png">


    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

</head>

<body style="background: #de520d">
<?php
if($mosfoglalt)
{
    ?>
    <script>
        alert('Ezen a műszakon másvalaki már mosogatott.')
    </script>
<?php
}
?>
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
                <a class="navbar-brand" href="../../profil"><img alt="Brand" src="../../res/kepek/FoodEx_logo.png" style="height: 30px"></a>
            </div>

            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li><a href="../../jelentkezes">Jelentkezés műszakra <span class="sr-only">(current)</span></a></li>
                    <li class="active"><a href="../../pontok/userpont/?mosjelentk=1">Mosogattam!</a></li>
                    <li><a href="../../pontok">Pontozás</a></li>
                    <?php
                    if ($AktProfil->getUjMuszakJog() == 1) {
                        ?>
                        <li><a href="../../ujmuszak">Új műszak kiírása</a></li>
                        <?php
                    }
                    ?>
                </ul>
                <ul class="nav navbar-nav navbar-right p-t" style="margin-top: 8px">
                    <li>
                        <form action="../../profil/logout.php">
                            <button type="submit" class="btn btn-danger">Kijelentkezés</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="panel panel-default">
        <div class="panel-heading"><b><?php echo $MegjelenitettProfil->getNev(); ?></b><?php echo ($MosogatasJelentkezes) ? ' elvitt műszakjai' : ' pontjai'; ?></div>
        <div class="panel-body">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Műszak <span>(ID)</span></th>
                    <th>Idő</th>
                    <th>Pont</th>
                    <th>Fő</th>
                    <?php
                    if ($MosogatasJelentkezes) {
                        ?>
                        <th>Mosogatás</th>
                        <?php
                    } else {
                        ?>
                        <th>Létrehozta</th>
                        <?php
                    }
                    ?>
                </tr>
                </thead>
                <?php
                try {
                    $conn = Eszkozok\Eszk::initMySqliObject();

                    if (!$conn)
                        throw new \Exception('SQL hiba: $conn is \'false\'');


                    $MuszakLetszamok = array();//Cacheli az muszid - Létszám párokat a műszakok közül, hogy ne kelljen minden műszaknál új lekérdezés a létszámért
                    $MuszakKiirokNevei = array();//Cacheli az internal_id - Név párokat a kiírók közül, hogy ne kelljen minden műszaknál új lekérdezés a névért


                    $stmt = $conn->prepare("SELECT `muszid`, `mosogat` FROM `fxjelentk` WHERE `jelentkezo` = ? AND status = 1;");
                    if (!$stmt)
                        throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                    $buffInt = $MegjelenitettProfil->getInternalID();
                    $stmt->bind_param('s', $buffInt);

                    if ($stmt->execute()) {
                        $resultJelentk = $stmt->get_result();
                        if ($resultJelentk->num_rows > 0) {
                            $jelMuszakIDk = array();
                            $jelMosogatasok = array();

                            while ($rowJelentk = $resultJelentk->fetch_assoc()) {
                                $aktmuszidBuff = $conn->escape_string($rowJelentk['muszid']);
                                $jelMuszakIDk[] = $aktmuszidBuff;
                                $jelMosogatasok[$aktmuszidBuff] = $conn->escape_string($rowJelentk['mosogat']);
                            }

                            //var_dump($muszakIDk);

                            $vittMuszakIDk = array();

                            foreach ($jelMuszakIDk as $muszidakt) {
                                if (!array_key_exists($muszidakt, $MuszakLetszamok))
                                {
                                   $buff = Eszkozok\Eszk::GetTaroltMuszakAdatWithConn($muszidakt, false, $conn);
                                    if($buff != false)
                                        $MuszakLetszamok[$muszidakt] = $buff->letszam;
                                }


                                $stmt = $conn->prepare("SELECT * FROM `fxjelentk` WHERE `muszid` = ? AND `status` = 1 ORDER BY `ID` ASC;");
                                if (!$stmt)
                                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                                $stmt->bind_param('i', $muszidakt);

                                if ($stmt->execute()) {
                                    $resultKeret = $stmt->get_result();
                                    if ($resultKeret->num_rows > 0) {

                                        for ($i = 0; ($rowKeret = $resultKeret->fetch_assoc()) && isset($MuszakLetszamok[$muszidakt])  && $i < $MuszakLetszamok[$muszidakt]; ++$i) {
                                            //echo $i . ' - ' . $muszidakt . '<br>';


                                            if ($MegjelenitettProfil->getInternalID() == $rowKeret['jelentkezo']) {
                                                $vittMuszakIDk[] = $muszidakt;
                                                break;
                                            }

                                            // var_dump($rowKeret);

                                        }
                                    }
                                }

                            }
                            if (count($vittMuszakIDk) > 0) {
                                //`idoveg` < NOW() : Csak arra a műszakra kap pontot, ami már lezárult
                                //TODO: idoveg < now() - ból kivenni a TRUE-t
                                $stmt = $conn->prepare("SELECT * FROM `fxmuszakok` WHERE  (FALSE || `idoveg` < NOW()) AND `ID` IN (" . implode(',', $vittMuszakIDk) . ") ORDER BY `idokezd` DESC;");
                                if (!$stmt)
                                    throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                                if ($stmt->execute()) {
                                    $resultMuszak = $stmt->get_result();
                                    if ($resultMuszak->num_rows > 0) {
                                        while ($rowMuszak = $resultMuszak->fetch_assoc()) {
                                            //var_dump($rowMuszak);


                                            $idokezd = DateTime::createFromFormat("Y-m-d H:i:s", $rowMuszak['idokezd']);

                                            $idostringbuff = $idokezd->format('Y');
                                            $idostringbuff .= ' ';
                                            $idostringbuff .= $idokezd->format('m-d');
                                            $idostringbuff .= '<br>';
                                            $idostringbuff .= $idokezd->format('H:i');

                                            $idostringbuff .= ' - ';

                                            $idoveg = DateTime::createFromFormat("Y-m-d H:i:s", $rowMuszak['idoveg']);
                                            $idostringbuff .= $idoveg->format('H:i');


                                            if (!array_key_exists($rowMuszak['kiirta'], $MuszakKiirokNevei))
                                                $MuszakKiirokNevei[$rowMuszak['kiirta']] = Eszkozok\Eszk::GetTaroltProfilAdat($rowMuszak['kiirta'])->getNev();


                                            ?>

                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($rowMuszak['musznev']) . '  <span>(' . htmlspecialchars($rowMuszak['ID']) . ')</span>'; ?>
                                                </td>
                                                <td><?php echo $idostringbuff; ?></td>
                                                <td>
                                                    <?php
                                                    if ($jelMosogatasok[$rowMuszak['ID']] == 1) {
                                                        echo htmlspecialchars($rowMuszak['pont']) . ' + ' . htmlspecialchars($rowMuszak['mospont']) . ' pont';
                                                    } else {
                                                        echo htmlspecialchars($rowMuszak['pont']) . ' pont';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($rowMuszak['letszam']) . ' fő'; ?></td>
                                                <?php
                                                if ($MosogatasJelentkezes) {
                                                    ?>
                                                    <td>
                                                        <?php
                                                        if ($jelMosogatasok[$rowMuszak['ID']] == 1) {
                                                            ?>
                                                            <a href="?mosjelentk=1&muv=ujmoslead&mosmuszid=<?php echo htmlspecialchars($rowMuszak['ID']); ?>">
                                                                <i class="fa fa-minus-square-o fa-2x"></i></a>
                                                            <?php
                                                        } else {
                                                            ?>
                                                            <a href="?mosjelentk=1&muv=ujmosjel&mosmuszid=<?php echo htmlspecialchars($rowMuszak['ID']); ?>">
                                                                <i class="fa fa-plus-square-o fa-2x"></i></a>
                                                            <?php
                                                        }
                                                        ?>
                                                    </td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td><?php echo htmlspecialchars($MuszakKiirokNevei[$rowMuszak['kiirta']]); ?></td>
                                                    <?php
                                                }
                                                ?>
                                            </tr>
                                            <?php
                                        }
                                    }
                                } else
                                    throw new \Exception('$stmt->execute() 3 nem sikerült' . ' :' . $conn->error);
                            }
                        }
                    } else
                        throw new \Exception('$stmt->execute() 2 nem sikerült' . ' :' . $conn->error);
                } catch (\Exception $e) {
                    ob_clean();
                    Eszkozok\Eszk::dieToErrorPage('3014: ' . $e->getMessage());
                }
                ?>
            </table>
        </div>
    </div>
</div>

<script src='https://www.google.com/recaptcha/api.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

</body>
</html>


