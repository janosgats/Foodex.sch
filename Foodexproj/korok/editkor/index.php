<?php
session_start();

require_once __DIR__ . '/../../Eszkozok/Eszk.php';
require_once __DIR__ . '/../../Eszkozok/LoginValidator.php';
require_once __DIR__ . '/../../Eszkozok/param.php';
require_once __DIR__ . '/../../Eszkozok/navbar.php';
require_once __DIR__ . '/../../Eszkozok/entitas/Kor.php';

\Eszkozok\LoginValidator::AdminJog_DiesToErrorrPage();

$Hibauzenet = '';
$HibasBemenet = false;
function hibasBemenet($hibuzenet)
{
    global $Hibauzenet;
    $Hibauzenet = $hibuzenet;

    global $HibasBemenet;
    $HibasBemenet = true;
}


$KorSzerkesztes = false;

$conn;
if (IsURLParamSet('felvetel') || IsURLParamSet('mentes') || IsURLParamSet('torles'))
{
    if (IsURLParamSet('felvetel'))
    {
        if (IsURLParamSet('nev') && ($nev = GetURLParam('nev')) != '')
        {
            if (strlen($nev) < 200)
            {

                try
                {
                    $conn = \Eszkozok\Eszk::initMySqliObject();

                    $stmt = $conn->prepare("INSERT INTO `korok` (`nev`) VALUES ( ? );");
                    $stmt->bind_param("s", $nev);

                    if ($stmt->execute())
                    {
                        \Eszkozok\Eszk::RedirectUnderRoot('korok');
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
                hibasBemenet('A név túl hosszú!');
        }
        else
            hibasBemenet('Adj meg nevet!');
    }
    else if (IsURLParamSet('mentes'))
    {
        $KorSzerkesztes = true;
        if (IsURLParamSet('nev') && ($nev = GetURLParam('nev')) != '')
        {
            if (strlen($nev) < 200)
            {

                try
                {
                    if (!IsURLParamSet('korid') || !is_numeric($id = GetURLParam('korid')) || $id < 0)
                        throw new Exception('3213: Sikertelen szerkesztés. Hibás $id');

                    $conn = \Eszkozok\Eszk::initMySqliObject();

                    $stmt = $conn->prepare("UPDATE `korok` SET `nev` = ? WHERE `id` = ? ");
                    $stmt->bind_param("si", $nev, $id);

                    if ($stmt->execute())
                    {
                        \Eszkozok\Eszk::RedirectUnderRoot('korok');
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

                    $stmt = $conn->prepare("DELETE FROM `korok` WHERE id = ?");
                    $stmt->bind_param("i", $id);

                    if ($stmt->execute())
                    {
                        if ($stmt->affected_rows != 1)
                            throw new Exception('5676: Sikertelen törlés. $stmt->affected_rows != 1');
                        else
                            \Eszkozok\Eszk::RedirectUnderRoot('korok');
                    }
                    else
                        throw new Exception('5677: Sikertelen törlés. $stmt->execute() is false');
                }
                else
                    throw new Exception("5678: Sikertelen törlés. A kör ID nem megfelelő! id: " . htmlspecialchars($id));
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


$SzerkesztendoKor = new \Eszkozok\Kor();

if (!$HibasBemenet)
{

    if (IsURLParamSet('szerk') && GetURLParam('szerk') == 1)
    {
        if (IsURLParamSet('korid'))
        {
            $korid = GetURLParam('korid');

            $SzerkesztendoKor = \Eszkozok\Eszk::GetTaroltKorAdat($korid, true);

            if ($SzerkesztendoKor != null)
                $KorSzerkesztes = true;

        }
    }

}
else
{
    $SzerkesztendoKor->id = '';
    $SzerkesztendoKor->nev = '';

    if (IsURLParamSet('korid'))
        $SzerkesztendoKor->id = GetURLParam('korid');
    if (IsURLParamSet('nev'))
        $SzerkesztendoKor->nev = GetURLParam('nev');
}

$MezokFeltoltendoek = $HibasBemenet || $KorSzerkesztes;

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Kör Kezelése</title>

    <link rel="icon" href="../../res/kepek/favicon1_64p.png">

    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'>


    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>

    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js'></script>
    <!--    <script src='/i2h/res/bootstrap/jquery.bootstrap-touchspin.js'></script>-->

</head>
<body style="background-color: #de520d">


<div class="container">

    <?php
    NavBar::echonavbar('korok');
    ?>

    <div class="jumbotron" style="padding-top: 5px">

        <h3><?php echo ($KorSzerkesztes) ? htmlspecialchars($SzerkesztendoKor->nev) . ' adatainak módosítása' : 'Új kör felvétele'; ?></h3>
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
                <input name="korid" value="<?php echo htmlspecialchars($SzerkesztendoKor->id); ?>" hidden>
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

                <div class="form-group col-md-12 col-sm-12">
                    <label for="nev">Név</label>
                    <input id="nev" name="nev" type="text" placeholder="pl. Mexicano"
                           value="<?php if ($MezokFeltoltendoek)
                           {
                               echo htmlspecialchars($SzerkesztendoKor->nev);

                           } ?>" class="form-control" autofocus>
                </div>


            </div>


            <div class="row" style="padding-right: 7%">
                <?php
                if (!$KorSzerkesztes)
                {
                    ?>
                    <button class="btn btn-success pull-right" name="felvetel" id="felvetel" value="1" type="submit">Kör felvétele</button>
                    <?php
                }
                else
                {
                    ?>
                    <button class="btn btn-primary pull-right" name="mentes" id="mentes" value="1" type="submit">Mentés</button>
                    <button class="btn btn-danger pull-right" name="torles" id="torles" value="1" style="margin-right: 10px" type="submit">Kör eltávolítása</button>
                    <?php
                }
                ?>
            </div>

        </form>

    </div>
</div>


<script>
    function escapeHtml(unsafe)
    {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

</script>

</body>
</html>