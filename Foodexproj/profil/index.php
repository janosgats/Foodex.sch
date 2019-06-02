<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once __DIR__ . '/../Eszkozok/param.php';
require_once __DIR__ . '/../Eszkozok/entitas/Profil.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';

\Eszkozok\LoginValidator::AccountSignedIn_RedirectsToRoot();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

if (IsURLParamSet('mprof'))
{
    $mprof_int_id = GetURLParam('mprof');
    $MegjProfil = \Eszkozok\Eszk::GetTaroltProfilAdat($mprof_int_id, true);
}
else
{
    $MegjProfil = $AktProfil;
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
    <title>Fx Profil</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

<!--    <meta name="viewport" content="width=device-width, initial-scale=100">-->
    <meta name="viewport" content="width=device-width">


    <link rel="stylesheet" href="main.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel='stylesheet' href='../vendor/kartik-v/bootstrap-star-rating/css/star-rating.css'>
    <link rel='stylesheet' href='../vendor/kartik-v/bootstrap-star-rating/themes/krajee-fas/theme.css'>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js'></script>
    <script src="../vendor/kartik-v/bootstrap-star-rating/js/star-rating.js"></script>
    <script src="../js/star-rating/locale-hu.js"></script>


</head>

<body style="background-color: #de520d">

<div class="container" style="min-width: 520px">

    <?php
    NavBar::echonavbar('');
    ?>

    <div class="jumbotron" >
        <h1 style="display:inline"><?php echo $MegjProfil->getNev(); ?></h1>
        <?php
        if ($MegjProfil->getInternalID() == 'efb8476b-46c2-7aa8-b612-46d3b3a84e4c')//Wuki Internal ID-je
        {
            ?>
            <h3 style="display:inline; color: #777777"> &nbsp; (Tuti a világi spanja a gyereknek.)</h3>
            <?php
        }
        ?>

        <p>Értesítési cím: <b><?php echo $MegjProfil->getEmail(); ?></b></p>

        <?php
        if ($MegjProfil->getFxTag() == 1)//Ha a megjelenített profil NEM Fx tag, akkor NEM lehet pontja
        {
            if (\Eszkozok\LoginValidator::PontLatJog_NOEXIT() || ($MegjProfil->getInternalID() == $AktProfil->getInternalID()))
            {
                ?>

                <a style="cursor: pointer;" href="<?php echo '../pontok/userpont/?int_id=' . $MegjProfil->getInternalID(); ?>">
                    <p>Pontok: <b><?php
                            try
                            {
                                $buff = \Eszkozok\Eszk::GetAccPontok($MegjProfil->getInternalID());
                                echo $buff;
                            }
                            catch (\Exception $e)
                            {
                                echo 'N/A';
                            } ?> pont</b></p></a>

                <?php
            }
        }
        ?>

        <p style="display: inline">Kedvenc vicc: </p>

        <p style="display: inline" id="kedv_vicc_szoveg"><?php
            $ProfInf = \Eszkozok\Eszk::GetTaroltProfilInfo($MegjProfil->getInternalID());

            if (isset($ProfInf->KedvencVicc) && $ProfInf->KedvencVicc != '')
                echo htmlspecialchars($ProfInf->KedvencVicc);
            else
                echo 'Semmi jó :(';

            ?>  </p>


        <?php
        if ($AktProfil->getInternalID() == $MegjProfil->getInternalID())
        {
            ?>
            <p id="ked_vicc_editgear" style="display: inline; font-size: x-large" onclick="StartEditKedvencVicc();"><i class="fa fa-cog fa settingsgear"></i></p>
            <?php
        }
        ?>

        <div class="input-group mb-3" style="display: none" id="kedv_vicc_editdiv">
            <input type="text" id="kedv_vicc_input" onblur="SubmitKedvencVicc()" class="form-control" placeholder="Fasza legyen ;)" aria-describedby="basic-addon2">
        </div>

        <br><br>

        <?php
        if (\Eszkozok\LoginValidator::AdminJog_NOEXIT())
        {
            ?>
            <a class="btn btn-primary pull-right" name="kompenz" id="kompenz" style="margin-right: 10px;"
               href="../ujkomp?<?php echo 'int_id=' . urlencode($MegjProfil->getInternalID()); ?>" type="button">Kompenzálás
            </a>

            <?php
        }
        ?>
<br>
    </div>

    <script>
        document.getElementById("kedv_vicc_input").addEventListener("keyup", function (e)
        {
            if (e.keyCode === 13)
            {  //checks whether the pressed key is "Enter"
                SubmitKedvencVicc();
            }
            else if (e.keyCode === 27)
            {  //checks whether the pressed key is "Esc"
                RestoreEditing();
            }
        });


        function StartEditKedvencVicc()
        {
            EnableSubmit = true;
            document.getElementById('ked_vicc_editgear').style.display = 'none';
            document.getElementById('kedv_vicc_szoveg').style.display = 'none';
            document.getElementById('kedv_vicc_editdiv').style.display = 'block';
            document.getElementById('kedv_vicc_input').focus();

            document.getElementById('kedv_vicc_input').value = ViccText;


        }

        var EnableSubmit = true;

        var ViccText = '<?php
            if (isset($ProfInf->KedvencVicc) && $ProfInf->KedvencVicc != '')
                echo htmlentities($ProfInf->KedvencVicc);
            ?>';

        function SubmitKedvencVicc()
        {
            if (EnableSubmit)
            {
                ViccText = document.getElementById("kedv_vicc_input").value;

                callPHPPage({
                    vicctext: ViccText,
                    megj_int_id: '<?php echo $MegjProfil->getInternalID(); ?>'
                });
            }
            EnableSubmit = false;
        }

        function RestoreEditing()
        {
            EnableSubmit = false;
            document.getElementById('kedv_vicc_szoveg').innerHTML = (ViccText == '') ? 'Semmi jó :(' : escapeHtml(ViccText);

            document.getElementById('kedv_vicc_editdiv').style.display = 'none';
            document.getElementById('ked_vicc_editgear').style.display = 'inline';
            document.getElementById('kedv_vicc_szoveg').style.display = 'inline';

            document.getElementById('kedv_vicc_input').blur();
        }

        function escapeHtml(unsafe)
        {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function HandlePHPPageData(ret)
        {

            switch (ret)
            {
                case 'tulhosszuvicc':
                    alert('A vicc túl hosszú :/');
                    break;

                case 'siker345':
                {
                    RestoreEditing();
                }
                    break;

                default:
                    alert(escapeHtml(ret));
            }

            EnableSubmit = true;
        }

        function callPHPPage(postdata)
        {
            $.post('editvicc.php', postdata, HandlePHPPageData).fail(
                function ()
                {
                    alert("Error at AJAX call!");
                });

            console.log('AJAX request sent.')
        }

    </script>
    <?php
    if ($MegjProfil->getFxTag() == 1)//Ha a megjelenített profil NEM Fx tag, akkor NEM lehetnek kompenzációi
    {
        if (\Eszkozok\LoginValidator::PontLatJog_NOEXIT())
        {
            ?>
            <div class="panel panel-default">
                <div class="panel-heading">

                    Kompenzációk
                </div>
                <div class="panel-body">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Pont</th>
                            <th>Megjegyzés</th>

                            <?php
                            if (\Eszkozok\LoginValidator::AdminJog_NOEXIT())
                            {
                                ?>
                                <th></th>

                                <?php
                            }
                            ?>
                        </tr>
                        </thead>
                        <?php
                        try
                        {
                            $conn = \Eszkozok\Eszk::initMySqliObject();
                            $stmt = $conn->prepare("SELECT * FROM `kompenz` WHERE `internal_id` = ? ORDER BY `ido` DESC;");
                            if (!$stmt)
                                throw new \Exception('SQL hiba: $stmt is \'false\'' . ' :' . $conn->error);

                            $buffInt = $MegjProfil->getInternalID();
                            $stmt->bind_param('s', $buffInt);

                            if ($stmt->execute())
                            {
                                $resultKomp = $stmt->get_result();
                                if ($resultKomp->num_rows > 0)
                                {
                                    while ($rowKomp = $resultKomp->fetch_assoc())
                                    {
                                        ?>

                                        <tr <?php echo (\Eszkozok\Eszk::IsDatestringInPontozasiIdoszak($rowKomp['ido'])) ? '' : 'style="background-color: #EEEEEE;color: grey"'; ?>>
                                            <td>
                                                <?php echo htmlentities($rowKomp['pont']) . ' pont'; ?>
                                            </td>
                                            <td>
                                                <?php echo htmlentities($rowKomp['megj']); ?>
                                            </td>

                                            <?php
                                            if (\Eszkozok\LoginValidator::AdminJog_NOEXIT())
                                            {
                                                ?>
                                                <td>
                                                    <p>
                                                        <a href="../ujkomp?szerk=1&kompid=<?php echo $rowKomp['ID']; ?>"
                                                           target="_blank"
                                                           style="text-decoration: none; color: inherit">
                                                            <i class="fa fa-cog fa-2x settingsgear"></i>
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
                                throw new \Exception('$stmt->execute() 2 nem sikerült' . ' :' . $conn->error);
                        }
                        catch (\Exception $e)
                        {
                            \Eszkozok\Eszk::dieToErrorPage('45848: ' . $e->getMessage(), 'profil');
                        }
                        ?>
                    </table>
                </div>
            </div>
            <?php
        }
    }
    ?>
    <?php
    if ($MegjProfil->getFxTag() == 1)//Ha a megjelenített profil NEM Fx tag, akkor NEM lehetnek kompenzációi
    {
        ?>
        <div class="panel panel-default">
            <div class="panel-heading">

                Értékelések
            </div>
            <div class="panel-body">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th class="ErtekelesTableheader" >Műszak</th>
                        <th class="ErtekelesTableheader">Értékelő</th>
                        <th class="ErtekelesTableheader" style="min-width: 200px;">Értékelés</th>

                    </tr>
                    </thead>
                    <?php
                    try
                    {
                        $conn = \Eszkozok\Eszk::initMySqliObject();

                        $stmt = $conn->prepare("SELECT
                        fxmuszakok.musznev AS MuszNev,
                        fxmuszakok.idokezd AS MuszIdoKezd,
                        fxmuszakok.id AS MuszId,
                        ertekelesek.*,
                        fxaccok.nev AS ErtekeloNev
                        FROM `ertekelesek`
                        JOIN `fxmuszakok` ON ertekelesek.Muszid = fxmuszakok.id
                        JOIN fxaccok ON fxaccok.internal_id = ertekelesek.ertekelo
                        WHERE `ertekelt` = ? ORDER BY `ertekelesek`.`id` DESC;");

                        $buffInt = $MegjProfil->getInternalID();
                        $stmt->bind_param('s', $buffInt);


                        if ($stmt->execute())
                        {
                            $resultErt = $stmt->get_result();
                            if ($resultErt->num_rows > 0)
                            {
                                while ($rowErt = $resultErt->fetch_assoc())
                                {
                                    ?>

                                    <tr>
                                        <td class="ErtekelesColumnMuszak">
                                            <p><?php echo htmlentities($rowErt['MuszNev'] ?: 'N/A') . ' (' . htmlentities($rowErt['muszid'] ?: 'N/A') . ')'; ?></p>

                                            <?php

                                            if ($rowErt['MuszIdoKezd'])
                                            {
                                                $MuszKezdDate = new DateTime($rowErt['MuszIdoKezd']);
                                                ?>
                                                <p><?php echo htmlentities($MuszKezdDate->format('Y-m-d')); ?></p>
                                                <p><?php echo htmlentities($MuszKezdDate->format('H:i')); ?></p>
                                                <?php
                                            }
                                            ?>
                                        </td>
                                        <td class="ErtekelesColumnErtekelo">
                                            <?php echo htmlentities($rowErt['ErtekeloNev']); ?>
                                        </td>
                                        <td>
                                            <div style="width: 100%;">
                                                <div style="display: table; margin: 0 auto;">
                                                    <table>
                                                        <tr>
                                                            <td>
                                                                <p><b>Pontosság:&nbsp;&nbsp;&nbsp;</b></p>
                                                            </td>
                                                            <td class="ErtekelesTartalomPontokOszlop">
                                                                <input class="rating-loading" value="<?php echo htmlentities($rowErt['e_pontossag'] ?: ''); ?>"
                                                                       data-min="0" data-max="5" data-step="0.5" data-size="sm" data-language="hu" data-theme="krajee-fas">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <p><b>Pénzkezelés:</p>
                                                            </td>
                                                            <td class="ErtekelesTartalomPontokOszlop">
                                                                <input class="rating-loading" value="<?php echo htmlentities($rowErt['e_penzkezeles'] ?: ''); ?>"
                                                                       data-min="0" data-max="5" data-step="0.5" data-size="sm" data-language="hu" data-theme="krajee-fas">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <p><b>Szakértelem: </b></p>
                                                            </td>
                                                            <td class="ErtekelesTartalomPontokOszlop">
                                                                <input class="rating-loading" value="<?php echo htmlentities($rowErt['e_szakertelem'] ?: ''); ?>"
                                                                       data-min="0" data-max="5" data-step="0.5" data-size="sm" data-language="hu" data-theme="krajee-fas">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <p><b>Dugnám:&nbsp;&nbsp;&nbsp;&nbsp; </b></p>
                                                            </td>
                                                            <td class="ErtekelesTartalomPontokOszlop">
                                                                <input class="rating-loading" value="<?php echo htmlentities($rowErt['e_dughatosag'] ?: ''); ?>"
                                                                       data-min="0" data-max="5" data-step="0.5" data-size="sm" data-language="hu" data-theme="krajee-fas">
                                                            </td>
                                                        </tr>
                                                        </tr>
                                                    </table>
                                                    <span><b>Szöveges értékelés:</b> <?php echo htmlentities($rowErt['e_szoveg'] ?: 'N/A'); ?></span>
                                                </div>
                                            </div>
                                        </td>


                                    </tr>
                                    <?php
                                }
                            }
                        }
                        else
                            throw new \Exception('$stmt->execute() 2 nem sikerült' . ' :' . $conn->error);
                    }
                    catch (\Exception $e)
                    {
                        \Eszkozok\Eszk::dieToErrorPage('45842: ' . $e->getMessage(), 'profil');
                    }
                    ?>
                </table>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<script>
    $(document).on('ready', function ()
    {
        $('.rating-loading').rating({displayOnly: true});
    });
</script>

</body>
</html>