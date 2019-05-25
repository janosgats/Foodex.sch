<?php
session_start();

require_once __DIR__ . '/../../Eszkozok/Eszk.php';
require_once __DIR__ . '/../../Eszkozok/param.php';
require_once __DIR__ . '/../../Eszkozok/navbar.php';
require_once __DIR__ . '/../../Eszkozok/entitas/Kor.php';

\Eszkozok\Eszk::ValidateLogin();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

if ($AktProfil->getAdminJog() != 1)
    Eszkozok\Eszk::RedirectUnderRoot('');

$Hibauzenet = '';
$HibasBemenet = false;
function hibasBemenet($hibuzenet)
{
    global $Hibauzenet;
    $Hibauzenet = $hibuzenet;

    global $HibasBemenet;
    $HibasBemenet = true;
}

function verifyDate($date, $strict = true)
{
    $dateTime = DateTime::createFromFormat('Y-m-d', $date);
    if ($strict)
    {
        $errors = DateTime::getLastErrors();
        if (!empty($errors['warning_count']))
        {
            return false;
        }
    }

    return $dateTime !== false;
}



$SzemSzerkesztes = false;

$conn;
if (IsURLParamSet('felvetel') || IsURLParamSet('mentes') || IsURLParamSet('torles'))
{
    if (IsURLParamSet('felvetel'))
    {
        if (IsURLParamSet('nev') && ($nev = GetURLParam('nev')) != '')
        {
            if (strlen($nev) < 600)
            {
                if (IsURLParamSet('lakcim') && ($lakcim = GetURLParam('lakcim')) != '')
                {
                    if (strlen($lakcim) < 1200)
                    {
                        if (IsURLParamSet('szuldat') && ($szuldat = GetURLParam('szuldat')) != '')
                        {
                            if (verifyDate($szuldat))
                            {
                                try
                                {
                                    $conn = \Eszkozok\Eszk::initMySqliObject();

                                    $stmt = $conn->prepare("INSERT INTO `szemely` (`nev`, `lakcim`, `szuldat`) VALUES ( ?, ?, ? ) ");
                                    $stmt->bind_param("sss", $nev, $lakcim, $szuldat);

                                    if ($stmt->execute())
                                    {
                                        \Eszkozok\Eszk::RedirectUnderRoot('szemelyek');
                                    }
                                    else
                                        throw new Exception('3244: Sikertelen felvétel. $stmt->execute() is false');

                                }
                                catch (\Exception $e)
                                {
                                    \Eszkozok\Eszk::dieToErrorPage($e->getMessage());
                                }
                                finally
                                {
                                    $conn->close();
                                }
                            }
                            else
                                hibasBemenet('A születési dátum helytelen!');
                        }
                        else
                            hibasBemenet('Adj meg születési dátumot!');
                    }
                    else
                        hibasBemenet('A lakcím túl hosszú!');

                }
                else
                    hibasBemenet('Adj meg lakcímet!');
            }
            else
                hibasBemenet('A név túl hosszú!');

        }
        else
            hibasBemenet('Adj meg nevet!');
    }
    else if (IsURLParamSet('mentes'))
    {
        $SzemSzerkesztes = true;
        if (IsURLParamSet('nev') && ($nev = GetURLParam('nev')) != '')
        {
            if (strlen($nev) < 600)
            {
                if (IsURLParamSet('lakcim') && ($lakcim = GetURLParam('lakcim')) != '')
                {
                    if (strlen($lakcim) < 1200)
                    {
                        if (IsURLParamSet('szuldat') && ($szuldat = GetURLParam('szuldat')) != '')
                        {
                            if (verifyDate($szuldat))
                            {
                                try
                                {
                                    if(!IsURLParamSet('korid') || !is_numeric($id = GetURLParam('korid')) || $id < 0)
                                        throw new Exception('3213: Sikertelen szerkesztés. Hibás $id');

                                    $conn = \Eszkozok\Eszk::initMySqliObject();

                                    $stmt = $conn->prepare("UPDATE `szemely` SET `nev` = ?, `lakcim` = ?, `szuldat` = ? WHERE `id` = ? ");
                                    $stmt->bind_param("sssi", $nev, $lakcim, $szuldat, $id);

                                    if ($stmt->execute())
                                    {
                                        \Eszkozok\Eszk::RedirectUnderRoot('szemelyek');
                                    }
                                    else
                                        throw new Exception('3214: Sikertelen szerkesztés. $stmt->execute() is false');

                                }
                                catch (\Exception $e)
                                {
                                    \Eszkozok\Eszk::dieToErrorPage($e->getMessage());
                                }
                                finally
                                {
                                    $conn->close();
                                }
                            }
                            else
                                hibasBemenet('A születési dátum helytelen!');
                        }
                        else
                            hibasBemenet('Adj meg születési dátumot!');
                    }
                    else
                        hibasBemenet('A lakcím túl hosszú!');

                }
                else
                    hibasBemenet('Adj meg lakcímet!');
            }
            else
                hibasBemenet('A név túl hosszú!');

        }
        else
            hibasBemenet('Adj meg nevet!');

    }
    else if (IsURLParamSet('torles'))
    {
        try
        {
            if (IsURLParamSet('korid'))
            {
                $id = GetURLParam('korid');

                if (is_numeric($id) && $id >= 0)
                {
                    $conn = \Eszkozok\Eszk::initMySqliObject();

                    $stmt = $conn->prepare("DELETE FROM `szemely` WHERE id = ?");
                    $stmt->bind_param("i", $id);

                    if ($stmt->execute())
                    {
                        if ($stmt->affected_rows != 1)
                            throw new Exception('5676: Sikertelen törlés. $stmt->affected_rows != 1');
                        else
                            \Eszkozok\Eszk::RedirectUnderRoot('szemelyek');
                    }
                    else
                        throw new Exception('5677: Sikertelen törlés. $stmt->execute() is false');
                }
                else
                    throw new Exception("5678: Sikertelen törlés. A személy ID nem megfelelő! id: " . htmlspecialchars($id));
            }
        }
        catch (\Exception $e)
        {
            \Eszkozok\Eszk::dieToErrorPage($e->getMessage());
        }
        finally
        {
            $conn->close();
        }
    }

}


$SzerkesztendoSzem = new Szemely();

if (!$HibasBemenet)
{

    if (IsURLParamSet('szerk') && GetURLParam('szerk') == 1)
    {
        if (IsURLParamSet('korid'))
        {
            $korid = GetURLParam('korid');

            $SzerkesztendoSzem = \Eszkozok\Eszk::GetTaroltSzemelyAdat($korid);

            if ($SzerkesztendoSzem != null)
                $SzemSzerkesztes = true;

        }
    }

}
else
{
    $SzerkesztendoSzem->id = '';
    $SzerkesztendoSzem->nev = '';
    $SzerkesztendoSzem->lakcim = '';
    $SzerkesztendoSzem->szuldat = '';

    if (IsURLParamSet('korid'))
        $SzerkesztendoSzem->id = GetURLParam('korid');
    if (IsURLParamSet('nev'))
        $SzerkesztendoSzem->nev = GetURLParam('nev');
    if (IsURLParamSet('lakcim'))
        $SzerkesztendoSzem->lakcim = GetURLParam('lakcim');
    if (IsURLParamSet('szuldat'))
        $SzerkesztendoSzem->szuldat = GetURLParam('szuldat');
}

$MezokFeltoltendoek = $HibasBemenet || $SzemSzerkesztes;

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Személy Kezelése</title>

    <link rel="icon" href="../res/kepek/kilometerora_64.png">

    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel='stylesheet prefetch'
          href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/css/bootstrap-datetimepicker.min.css'>

    <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'>

    <link rel='stylesheet prefetch' href="/i2h/res/stylesheet/default.css">


    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>

    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.min.js'></script>
    <!--    <script src='/i2h/res/bootstrap/jquery.bootstrap-touchspin.js'></script>-->


    <script
        src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/js/bootstrap-datetimepicker.min.js'></script>

</head>
<body>


<div class="container">

    <?php
    NavBar::echonavbar('szemelyek');
    ?>

    <div class="jumbotron" style="padding-top: 5px">

        <h3><?php echo ($SzemSzerkesztes) ? htmlspecialchars($SzerkesztendoSzem->nev) . ' adatainak módosítása' : 'Új személy felvétele'; ?></h3>
        <?php
        if ($Hibauzenet != '')
        {
            ?>
            <h4 style="color: red;"><?php echo $Hibauzenet; ?></h4>
            <?php
        }
        ?>
        <hr>
        <form method="get" action="">

            <?php
            if ($MezokFeltoltendoek)
            {
                ?>
                <input name="korid" value="<?php echo htmlspecialchars($SzerkesztendoSzem->id); ?>" hidden>
                <?php
            }
            ?>

            <!--            <div class="row">-->
            <!--                <div class="form-group col-md-6 col-sm-12">-->
            <!---->
            <!--                    <label for="pont">Pont (Akár negatív)</label>-->
            <!---->
            <!--                    <input id="pont" type="text" value="--><?php //if ($GyorshSzerkesztes)
            //                    {
            //                        echo 69;
            //                    }
            //                    else
            //                    {
            //                        echo '0';
            //                    } ?><!--" name="pont">-->
            <!--                    <script>-->
            <!--                        $("input[name='pont']").TouchSpin({-->
            <!--                            min: -999999,-->
            <!--                            max: 999999,-->
            <!--                            step: 0.1,-->
            <!--                            decimals: 2,-->
            <!--                            boostat: 0.2,-->
            <!--                            maxboostedstep: 0.5,-->
            <!--                            postfix: 'pont'-->
            <!--                        });-->
            <!--                    </script>-->
            <!--                </div>-->
            <!---->
            <!--                <div class="form-group col-md-6 col-sm-12">-->
            <!--                    <label for="megj">Megjegyzés</label>-->
            <!--                    <input id="megj" name="megj" type="text" placeholder="pl. Nem jelent meg a műszakon."-->
            <!--                           value="--><?php //if ($GyorshSzerkesztes)
            //                           {
            //                               echo '$SzerkesztendoGyorsh->megj';
            //                           } ?><!--" class="form-control">-->
            <!--                </div>-->
            <!---->
            <!--            </div>-->

            <div class="row">

                <div class="form-group col-md-6 col-sm-12">
                    <label for="nev">Név</label>
                    <input id="nev" name="nev" type="text" placeholder="pl. Recská Zoltán"
                           value="<?php if ($MezokFeltoltendoek)
                           {
                               echo htmlspecialchars($SzerkesztendoSzem->nev);

                           } ?>" class="form-control">
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label for="szuldat">Születési dátum</label>

                    <div class="input-group date">
                        <input type="text" class="form-control" id="szuldat" name="szuldat"
                               placeholder="YYYY-MM-DD" value="<?php if ($MezokFeltoltendoek)
                        {
                            echo htmlspecialchars($SzerkesztendoSzem->szuldat);
                        } ?>"/>
                        <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    </div>
                </div>

            </div>
            <div class="row">


                <div class="form-group col-md-12 col-sm-12">
                    <label for="lakcim">Lakcím</label>
                    <input id="lakcim" name="lakcim" type="text" placeholder="pl. Humbákfalva, Tibi tér 13."
                           value="<?php if ($MezokFeltoltendoek)
                           {
                               echo htmlspecialchars($SzerkesztendoSzem->lakcim);
                           } ?>" class="form-control">
                </div>


            </div>

            <div class="row" style="padding-right: 7%">
                <?php
                if (!$SzemSzerkesztes)
                {
                    ?>
                    <button class="btn btn-success pull-right" name="felvetel" id="felvetel" value="1" type="submit">Személy felvétele</button>
                    <?php
                }
                else
                {
                    ?>
                    <button class="btn btn-primary pull-right" name="mentes" id="mentes" value="1" type="submit">Mentés</button>
                    <button class="btn btn-danger pull-right" name="torles" id="torles" value="1" style="margin-right: 10px" type="submit">Személy eltávolítása</button>
                    <?php
                }
                ?>
            </div>

        </form>

    </div>
</div>


<script>
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

</script>

<script>
    $(document).ready(function () {
        var bindDatetimePicker = function (id) {
            var date_input = $('input[name=' + id + ']'); //our date input has the name "szuldat"
            var container = $('.bootstrap-iso form').length > 0 ? $('.bootstrap-iso form').parent() : "body";

            date_input.datetimepicker({
                format: 'YYYY-MM-DD',
                container: container,
                todayHighlight: true,
                autoclose: true,
//                sideBySide: true,
                showTodayButton: true,
                pickTime: false,
                locale: 'hu'
            });
        };

        bindDatetimePicker("szuldat");
    });
</script>

</body>
</html>